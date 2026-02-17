<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $leadsQuery = Lead::with(['service', 'status', 'assignedUser', 'creator', 'leadDetails' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        if (!$user->isAdmin()) {
            $leadsQuery->where('assigned_user_id', $user->id);
        }

        $leads = $leadsQuery->orderBy('created_at', 'desc')->get();

        // Ensure lead details are properly loaded and appended
        $leads->load(['leadDetails' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Explicitly append leadDetails to each lead for frontend
        $leads->each(function($lead) {
            $lead->makeVisible(['lead_details']);
            $lead->setRelation('lead_details', $lead->leadDetails);
        });

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'user' => $user,
            'services' => Service::all(),
            'statuses' => Status::all(),
            'users' => User::all(),
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

        // Check for duplicates
        $duplicateQuery = Lead::where(function($query) use ($request) {
            if ($request->phone) {
                $query->where('phone', $request->phone);
            }
            if ($request->email) {
                if ($request->phone) {
                    $query->orWhere('email', $request->email);
                } else {
                    $query->where('email', $request->email);
                }
            }
        });

        if ($duplicateQuery->exists()) {
            return back()->withErrors(['duplicate' => 'A lead with this phone or email already exists.'])->withInput();
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

        return redirect()->route('leads.index')->with('success', 'Lead created successfully!');
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
            'statuses' => Status::all(),
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
        $statuses = Status::all();
        $users = User::where('role', 'user')->get();

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

        // Check for duplicates (excluding current lead)
        $duplicateQuery = Lead::where('id', '!=', $lead->id)
            ->where(function($query) use ($request) {
                if ($request->phone) {
                    $query->where('phone', $request->phone);
                }
                if ($request->email) {
                    if ($request->phone) {
                        $query->orWhere('email', $request->email);
                    } else {
                        $query->where('email', $request->email);
                    }
                }
            });

        if ($duplicateQuery->exists()) {
            return back()->withErrors(['duplicate' => 'A lead with this phone or email already exists.'])->withInput();
        }

        $lead->update([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
            'service_id' => $request->service_id,
            'status_id' => $request->status_id,
            'assigned_user_id' => $request->assigned_user_id,
        ]);

        return back()->with('success', 'Lead updated successfully!');
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

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully!');
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
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header if it exists (check if first row has "phone" or non-numeric data)
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

            // Check for duplicates
            if (Lead::where('phone', $phone)->exists()) {
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
                    'assigned_user_id' => auth()->id(),
                    'created_by' => auth()->id(),
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
