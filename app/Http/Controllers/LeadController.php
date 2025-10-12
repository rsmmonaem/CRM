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
        $duplicateQuery = Lead::query();
        if ($request->phone) {
            $duplicateQuery->orWhere('phone', $request->phone);
        }
        if ($request->email) {
            $duplicateQuery->orWhere('email', $request->email);
        }

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
        $duplicateQuery = Lead::where('id', '!=', $lead->id);
        if ($request->phone) {
            $duplicateQuery->orWhere('phone', $request->phone);
        }
        if ($request->email) {
            $duplicateQuery->orWhere('email', $request->email);
        }

        if ($duplicateQuery->exists()) {
            return back()->withErrors(['duplicate' => 'A lead with this phone or email already exists.'])->withInput();
        }

        $lead->update($request->all());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully!');
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
}
