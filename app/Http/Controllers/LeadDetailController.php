<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeadDetail;
use App\Models\Lead;

class LeadDetailController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(LeadDetail $leadDetail)
    {
        $user = auth()->user();

        // Check if user can view this detail
        // Admin users can view any detail
        // Regular users can only view details for leads assigned to them
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'view') && $leadDetail->lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to view call details for this lead.');
        }

        return response()->json($leadDetail);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeadDetail $leadDetail)
    {
        $user = auth()->user();

        // Check if user can edit this detail
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'edit') && $leadDetail->lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to edit call details for this lead.');
        }

        return response()->json($leadDetail);
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'call_followup_date' => 'required|date',
            'call_followup_summary' => 'required|string',
            'next_call_date' => 'nullable|date|after:call_followup_date',
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $user = auth()->user();

        // Check if user can add details to this lead
        // Admin users can add details to any lead
        // Regular users can only add details to leads assigned to them
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'create') && $lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to add call details to this lead.');
        }

        LeadDetail::create([
            'lead_id' => $request->lead_id,
            'call_followup_date' => $request->call_followup_date,
            'call_followup_summary' => $request->call_followup_summary,
            'next_call_date' => $request->next_call_date,
            'called_at' => now(), // Mark as called when creating
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Call completed successfully!');
    }

    /**
     * Mark a call as completed (for existing call details)
     */
    public function markAsCompleted(Request $request, LeadDetail $leadDetail)
    {
        $request->validate([
            'call_followup_summary' => 'required|string',
            'next_call_date' => 'nullable|date|after:today',
        ]);

        $user = auth()->user();

        // Check if user can update this detail
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'edit') && $leadDetail->lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to complete calls for this lead.');
        }

        $leadDetail->update([
            'call_followup_summary' => $request->call_followup_summary,
            'next_call_date' => $request->next_call_date,
            'called_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Call marked as completed successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeadDetail $leadDetail)
    {
        $request->validate([
            'call_followup_date' => 'required|date',
            'call_followup_summary' => 'required|string',
            'next_call_date' => 'nullable|date|after:call_followup_date',
        ]);

        $user = auth()->user();

        // Check if user can update this detail
        // Admin users can update any detail
        // Regular users can only update details for leads assigned to them
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'edit') && $leadDetail->lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to update call details for this lead.');
        }

        $leadDetail->update([
            'call_followup_date' => $request->call_followup_date,
            'call_followup_summary' => $request->call_followup_summary,
            'next_call_date' => $request->next_call_date,
        ]);

        return redirect()->back()->with('success', 'Call detail updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeadDetail $leadDetail)
    {
        $user = auth()->user();

        // Check if user can delete this detail
        // Admin users can delete any detail
        // Regular users can only delete details for leads assigned to them
        if (!$user->isAdmin() && !$user->hasPermission('leads', 'delete') && $leadDetail->lead->assigned_user_id !== $user->id) {
            abort(403, 'You do not have permission to delete call details for this lead.');
        }

        $leadDetail->delete();

        return redirect()->back()->with('success', 'Call detail deleted successfully!');
    }
}
