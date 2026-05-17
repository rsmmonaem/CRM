<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Static/Lookup Data (Cached)
        $services = Cache::remember('services_list', 86400, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 86400, fn() => Status::where('type', 'lead')->get());
        $call_statuses = Cache::remember('call_statuses_list', 86400, fn() => Status::where('type', 'call')->get());
        $users = Cache::remember('users_list', 86400, fn() => User::select('id', 'name')->get());

        // 2. Optimized Lead Query
        $leadsQuery = Lead::with(['service', 'status', 'assignedUser', 'creator', 'latestLeadDetail']);

        if (!$user->isAdmin()) {
            $leadsQuery->where('assigned_user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $leadsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service')) {
            $leadsQuery->where('service_id', $request->input('service'));
        }

        if ($request->filled('status')) {
            $leadsQuery->where('status_id', $request->input('status'));
        }

        if ($request->filled('dateFrom')) {
            $leadsQuery->whereDate('created_at', '>=', $request->input('dateFrom'));
        }

        if ($request->filled('dateTo')) {
            $leadsQuery->whereDate('created_at', '<=', $request->input('dateTo'));
        }

        if ($user->isAdmin() && $request->filled('user')) {
            $leadsQuery->where('assigned_user_id', $request->input('user'));
        }

        if ($request->filled('isCall')) {
            if ($request->input('isCall') === 'yes') {
                $leadsQuery->whereHas('leadDetails');
            } else {
                $leadsQuery->whereDoesntHave('leadDetails');
            }
        }

        if ($request->filled('callStatus')) {
            $leadsQuery->whereHas('latestLeadDetail', function($q) use ($request) {
                $q->where('call_status', $request->input('callStatus'));
            });
        }

        // Calculate stats
        $hasFilters = $request->anyFilled(['search', 'service', 'status', 'dateFrom', 'dateTo', 'user', 'isCall', 'callStatus']);
        
        if (!$hasFilters) {
            $stats = Cache::remember('leads_global_stats_' . ($user->isAdmin() ? 'admin' : 'user_' . $user->id), 300, function() use ($leadsQuery) {
                $q = clone $leadsQuery;
                return [
                    'total' => $q->count(),
                    'services' => (clone $q)->selectRaw('service_id, count(*) as total')->groupBy('service_id')->pluck('total', 'service_id'),
                    'statuses' => (clone $q)->selectRaw('status_id, count(*) as total')->groupBy('status_id')->pluck('total', 'status_id'),
                    'users' => (clone $q)->selectRaw('assigned_user_id, count(*) as total')->groupBy('assigned_user_id')->pluck('total', 'assigned_user_id'),
                ];
            });
        } else {
            $statsQuery = clone $leadsQuery;
            $stats = [
                'total' => $statsQuery->count(),
                'services' => (clone $statsQuery)->selectRaw('service_id, count(*) as total')->groupBy('service_id')->pluck('total', 'service_id'),
                'statuses' => (clone $statsQuery)->selectRaw('status_id, count(*) as total')->groupBy('status_id')->pluck('total', 'status_id'),
                'users' => (clone $statsQuery)->selectRaw('assigned_user_id, count(*) as total')->groupBy('assigned_user_id')->pluck('total', 'assigned_user_id'),
            ];
        }

        $totalLeads = $stats['total'];
        $perPageInput = $request->input('per_page', 50);
        
        if ($perPageInput === 'all') {
            $perPage = $totalLeads > 0 ? min($totalLeads, 1000) : 50;
        } else {
            $perPage = is_numeric($perPageInput) ? (int)$perPageInput : 50;
            if ($perPage <= 0) $perPage = 50;
        }

        $leads = $leadsQuery->withExists('leadDetails')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->all());

        // Map data for frontend compatibility
        $leads->getCollection()->transform(function($lead) {
            $lead->is_call = (bool)$lead->lead_details_exists;
            $lead->lead_details = $lead->latestLeadDetail ? [$lead->latestLeadDetail] : [];
            return $lead;
        });

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'call_statuses' => $call_statuses,
            'users' => $users,
            'filters' => $request->only(['search', 'service', 'status', 'dateFrom', 'dateTo', 'user', 'per_page', 'isCall', 'callStatus']),
            'stats' => $stats
        ]);
    }

    /**
     * Display leads with duplicate phone numbers (last 6 digits).
     */
    public function duplicates(Request $request)
    {
        $user = auth()->user();

        // 1. Static/Lookup Data (Cached)
        $services = Cache::remember('services_list', 3600, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 3600, fn() => Status::where('type', 'lead')->get());
        $call_statuses = Cache::remember('call_statuses_list', 3600, fn() => Status::where('type', 'call')->get());
        $users = Cache::remember('users_list', 3600, fn() => User::select('id', 'name')->get());

        // 2. Optimized Lead Query for duplicates
        // Find duplicate suffixes (last 10 digits) - Cached for performance
        $duplicateSuffixes = Cache::remember('leads_duplicate_suffixes', 600, function() {
            return Lead::selectRaw('RIGHT(phone, 10) as suffix')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->groupBy('suffix')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('suffix');
        });

        $leadsQuery = Lead::with(['service', 'status', 'assignedUser', 'creator', 'latestLeadDetail']);

        if ($duplicateSuffixes->isEmpty()) {
            $leadsQuery->where('id', 0); // Return empty results
        } else {
            $leadsQuery->whereRaw('RIGHT(phone, 10) IN (' . $duplicateSuffixes->map(fn($s) => "'$s'")->implode(',') . ')');
        }

        if (!$user->isAdmin()) {
            $leadsQuery->where('assigned_user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $leadsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service')) {
            $leadsQuery->where('service_id', $request->input('service'));
        }

        if ($request->filled('status')) {
            $leadsQuery->where('status_id', $request->input('status'));
        }

        if ($request->filled('dateFrom')) {
            $leadsQuery->whereDate('created_at', '>=', $request->input('dateFrom'));
        }

        if ($request->filled('dateTo')) {
            $leadsQuery->whereDate('created_at', '<=', $request->input('dateTo'));
        }

        if ($user->isAdmin() && $request->filled('user')) {
            $leadsQuery->where('assigned_user_id', $request->input('user'));
        }

        // Calculate stats
        $statsQuery = clone $leadsQuery;
        $totalLeads = $statsQuery->count();
        
        $perPageInput = $request->input('per_page', 50);
        if ($perPageInput === 'all') {
            $perPage = $totalLeads > 0 ? min($totalLeads, 1000) : 50;
        } else {
            $perPage = is_numeric($perPageInput) ? (int)$perPageInput : 50;
            if ($perPage <= 0) $perPage = 50;
        }

        $leads = $leadsQuery->withExists('leadDetails')
            ->orderByRaw('RIGHT(phone, 10)')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->all());

        // Map data for frontend compatibility
        $leads->getCollection()->transform(function($lead) {
            $lead->is_call = (bool)$lead->lead_details_exists;
            $lead->lead_details = $lead->latestLeadDetail ? [$lead->latestLeadDetail] : [];
            return $lead;
        });

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'call_statuses' => $call_statuses,
            'users' => $users,
            'filters' => $request->only(['search', 'service', 'status', 'dateFrom', 'dateTo', 'user', 'per_page']),
            'stats' => [
                'total' => $totalLeads,
                'services' => (clone $statsQuery)->selectRaw('service_id, count(*) as total')->groupBy('service_id')->pluck('total', 'service_id'),
                'statuses' => (clone $statsQuery)->selectRaw('status_id, count(*) as total')->groupBy('status_id')->pluck('total', 'status_id'),
                'users' => (clone $statsQuery)->selectRaw('assigned_user_id, count(*) as total')->groupBy('assigned_user_id')->pluck('total', 'assigned_user_id'),
            ],
            'title' => 'Duplicate Number Leads'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'service_id' => 'required|exists:services,id',
            'status_id' => 'required|exists:statuses,id',
            'assigned_user_id' => 'required|exists:users,id',
        ], [
            'email.email' => 'Please enter a valid email address.',
            'service_id.required' => 'Please select a service.',
            'service_id.exists' => 'Selected service is invalid.',
            'status_id.required' => 'Please select a status.',
            'status_id.exists' => 'Selected status is invalid.',
            'assigned_user_id.required' => 'Please select an assigned user.',
            'assigned_user_id.exists' => 'Selected user is invalid.',
        ]);

        // Custom validation: require either phone or email
        if (empty($request->phone) && empty($request->email)) {
            return back()->withErrors(['contact' => 'Either phone number or email address is required.'])->withInput();
        }

        // Check for duplicates (last 10 digits of phone)
        if ($request->phone) {
            $cleanPhone = preg_replace('/\D/', '', $request->phone);
            $suffix = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
            
            if (!empty($suffix) && Lead::whereRaw('RIGHT(phone, 10) = ?', [$suffix])->exists()) {
                return back()->withErrors(['phone' => 'duplicate number ok'])->withInput();
            }
        }

        // Check for email duplicates
        if ($request->email && Lead::where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'A lead with this email already exists.'])->withInput();
        }

        Lead::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'assigned_user_id' => $request->assigned_user_id,
            'created_by' => auth()->id(),
        ]);

        // Invalidate caches
        $this->clearLeadCaches();

        return redirect()->route('leads.index')->with('success', 'Lead created successfully!');
    }

    /**
     * Clear all lead related caches
     */
    private function clearLeadCaches()
    {
        Cache::forget('leads_duplicate_suffixes');
        // Clear dashboard stats
        Cache::flush();
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $user = auth()->user();

        // Check if user can view this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $lead->load(['service', 'status', 'assignedUser', 'creator', 'leadDetails.creator']);

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'user' => $user,
            'statuses' => Status::where('type', 'lead')->get(),
            'call_statuses' => Status::where('type', 'call')->get(),
            'services' => \App\Models\Service::all(),
            'users' => \App\Models\User::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $user = auth()->user();

        // Check if user can edit this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $services = Service::all();
        $statuses = Status::where('type', 'lead')->get();
        $users = User::select('id', 'name')->get();

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'services' => $services,
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Check if user can update this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'service_id' => 'required|exists:services,id',
            'status_id' => 'required|exists:statuses,id',
            'assigned_user_id' => 'required|exists:users,id',
        ]);

        // Custom validation: require either phone or email
        if (empty($request->phone) && empty($request->email)) {
            return back()->withErrors(['contact' => 'Either phone or email is required.'])->withInput();
        }

        // Check for duplicates (last 10 digits of phone) excluding current lead
        if ($request->phone) {
            $cleanPhone = preg_replace('/\D/', '', $request->phone);
            $suffix = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
            
            if (!empty($suffix) && Lead::where('id', '!=', $lead->id)->whereRaw('RIGHT(phone, 10) = ?', [$suffix])->exists()) {
                return back()->withErrors(['phone' => 'duplicate number ok'])->withInput();
            }
        }

        // Check for email duplicates excluding current lead
        if ($request->email && Lead::where('id', '!=', $lead->id)->where('email', $request->email)->exists()) {
            return back()->withErrors(['email' => 'A lead with this email already exists.'])->withInput();
        }

        $lead->update($request->only([
            'name', 'company_name', 'location', 'phone', 'email', 'service_id', 'status_id', 'assigned_user_id'
        ]));

        $this->clearLeadCaches();

        return back()->with('success', 'Lead updated successfully!');
    }

    /**
     * Update the lead status.
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Check if user can update this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
        ]);

        $lead->update([
            'status_id' => $request->status_id,
        ]);

        $this->clearLeadCaches();

        return back()->with('success', 'Lead status updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $user = auth()->user();

        // Check if user can delete this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $lead->delete();
        $this->clearLeadCaches();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully!');
    }

    /**
     * Remove the specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No leads selected for deletion.');
        }

        $query = Lead::whereIn('id', $ids);

        // If not admin, can only delete their own assigned leads
        if (!$user->isAdmin()) {
            $query->where('assigned_user_id', $user->id);
        }

        $count = $query->count();
        $query->delete();
        $this->clearLeadCaches();

        return redirect()->route('leads.index')->with('success', "{$count} leads deleted successfully!");
    }

    /**
     * Get lead details for API
     */
    public function getLeadDetails(Lead $lead)
    {
        $user = auth()->user();

        // Check if user can view this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $leadDetails = $lead->leadDetails()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'lead_details' => $leadDetails
        ]);
    }

    /**
     * Import leads from a CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'service_id' => 'required|exists:services,id',
            'status_id' => 'required|exists:statuses,id',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $currentUser = auth()->user();
        
        // Determine who to assign the leads to
        $assignedToId = $currentUser->id;
        if ($currentUser->isAdmin() && $request->filled('assigned_user_id')) {
            $assignedToId = $request->assigned_user_id;
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header if it exists
        $header = fgetcsv($handle);
        
        $importedCount = 0;
        $skippedCount = 0;
        $duplicateCount = 0;

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Assume first column is phone if only one column, 
            // otherwise try to find phone column or assume first is name, second is phone etc.
            // For simplicity and based on "only phone number is required", 
            // let's assume the CSV structure: Name, Company, Phone, Email
            // or just Phone.
            
            $name = count($data) > 0 ? $data[0] : null;
            $company = count($data) > 1 ? $data[1] : null;
            $phone = count($data) > 2 ? $data[2] : (count($data) > 0 ? $data[0] : null);
            $email = count($data) > 3 ? $data[3] : null;

            // Clean phone: keep only digits
            $phone = preg_replace('/[^0-9]/', '', $phone);

            if (empty($phone)) {
                $skippedCount++;
                continue;
            }

            // Check for duplicates (last 10 digits)
            $suffix = strlen($phone) >= 10 ? substr($phone, -10) : $phone;
            if (!empty($suffix) && Lead::whereRaw('RIGHT(phone, 10) = ?', [$suffix])->exists()) {
                $duplicateCount++;
                continue;
            }

            try {
                Lead::create([
                    'name' => $name ?: 'Imported Lead',
                    'company_name' => $company,
                    'phone' => $phone,
                    'email' => $email,
                    'service_id' => $request->service_id,
                    'status_id' => $request->status_id,
                    'assigned_user_id' => $assignedToId,
                    'created_by' => $currentUser->id,
                ]);
                $importedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
            }
        }

        fclose($handle);

        return redirect()->route('leads.index')->with('success', "Import completed: {$importedCount} leads imported, {$duplicateCount} duplicates skipped, {$skippedCount} invalid rows skipped.");
    }
}
