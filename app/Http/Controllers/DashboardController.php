<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\LeadDetail;
use App\Models\User;
use App\Models\Service;
use App\Models\Status;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        // CRM Flow Summary Implementation
        // 1. Today's Call: next_call_date = today and called_at IS NULL
        $todaysCallsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
        //lest next call date
            ->whereDate('next_call_date', $today)
            ->whereNull('called_at')
            ->orderBy('next_call_date', 'asc');

        // 2. Pending Call: next_call_date < today and called_at IS NULL
        $pendingCallsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '<', $today)
            ->whereNull('called_at')
            ->orderBy('next_call_date', 'asc');

        // 3. Upcoming Call: next_call_date > today and called_at IS NULL
        $upcomingCallsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '>', $today)
            ->whereNull('called_at')
            ->orderBy('next_call_date', 'asc');

        // Filter by user role
        if (!$user->isAdmin()) {
            $todaysCallsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });

            $pendingCallsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });

            $upcomingCallsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });
        }

        $todaysCalls = $todaysCallsQuery->get();
        $pendingCalls = $pendingCallsQuery->get();
        $upcomingCalls = $upcomingCallsQuery->get();


        // Get leads for the user
        $leadsQuery = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function($query) {
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

        // Debug: Check if lead details are loaded
        if ($leads->count() > 0) {
            $firstLead = $leads->first();
            \Log::info('Dashboard Debug - Lead ID: ' . $firstLead->id . ', Lead Details Count: ' . $firstLead->leadDetails->count());
            \Log::info('Dashboard Debug - Lead Details: ' . json_encode($firstLead->leadDetails->toArray()));
            \Log::info('Dashboard Debug - Lead Details Relation: ' . json_encode($firstLead->lead_details->toArray()));
        }

        // Get statistics
        $stats = [
            'total_leads' => $leads->count(),
            'todays_calls' => $todaysCalls->count(),
            'pending_calls' => $pendingCalls->count(),
            'upcoming_calls' => $upcomingCalls->count(),
            'leads_by_status' => $leads->groupBy('status.name')->map->count(),
        ];

        // Get users for admin filter
        $users = $user->isAdmin() ? User::where('role', 'user')->get() : collect();

        // Get services and statuses for the new lead modal
        $services = Service::all();
        $statuses = Status::all();
        $modalUsers = User::where('role', 'user')->get();

        return Inertia::render('Dashboard', [
            'todaysCalls' => $todaysCalls,
            'pendingCalls' => $pendingCalls,
            'upcomingCalls' => $upcomingCalls,
            'leads' => $leads,
            'stats' => $stats,
            'users' => $users,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'modalUsers' => $modalUsers,
        ]);
    }

    public function filterByUser(Request $request, $userId)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $selectedUser = User::findOrFail($userId);
        $today = Carbon::today();

        // CRM Flow Summary Implementation for selected user
        // 1. Today's Call: next_call_date = today and called_at IS NULL
        $todaysCalls = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', $today)
            ->whereNull('called_at')
            ->whereHas('lead', function($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // 2. Pending Call: next_call_date < today and called_at IS NULL
        $pendingCalls = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '<', $today)
            ->whereNull('called_at')
            ->whereHas('lead', function($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // 3. Upcoming Call: next_call_date > today and called_at IS NULL
        $upcomingCalls = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '>', $today)
            ->whereNull('called_at')
            ->whereHas('lead', function($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // Get leads for selected user
        $leads = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])
            ->where('assigned_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ensure lead details are properly loaded and appended
        $leads->load(['leadDetails' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Explicitly append leadDetails to each lead for frontend
        $leads->each(function($lead) {
            $lead->makeVisible(['lead_details']);
            $lead->setRelation('lead_details', $lead->leadDetails);
        });

        // Debug: Check if lead details are loaded
        if ($leads->count() > 0) {
            $firstLead = $leads->first();
            \Log::info('Dashboard Filter Debug - Lead ID: ' . $firstLead->id . ', Lead Details Count: ' . $firstLead->leadDetails->count());
            \Log::info('Dashboard Filter Debug - Lead Details: ' . json_encode($firstLead->leadDetails->toArray()));
            \Log::info('Dashboard Filter Debug - Lead Details Relation: ' . json_encode($firstLead->lead_details->toArray()));
        }

        // Get statistics
        $stats = [
            'total_leads' => $leads->count(),
            'todays_calls' => $todaysCalls->count(),
            'pending_calls' => $pendingCalls->count(),
            'upcoming_calls' => $upcomingCalls->count(),
            'leads_by_status' => $leads->groupBy('status.name')->map->count(),
        ];

        // Get all users for admin filter
        $users = User::where('role', 'user')->get();

        // Get services and statuses for the new lead modal
        $services = Service::all();
        $statuses = Status::all();
        $modalUsers = User::where('role', 'user')->get();

        return Inertia::render('Dashboard', [
            'todaysCalls' => $todaysCalls,
            'pendingCalls' => $pendingCalls,
            'upcomingCalls' => $upcomingCalls,
            'leads' => $leads,
            'stats' => $stats,
            'users' => $users,
            'user' => $user,
            'selectedUser' => $selectedUser,
            'services' => $services,
            'statuses' => $statuses,
            'modalUsers' => $modalUsers,
        ]);
    }
}
