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

        // Subquery to get the latest LeadDetail for each lead
        $latestLeadDetail = LeadDetail::select('id', 'lead_id', 'next_call_date', 'called_at')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                      ->from('lead_details')
                      ->groupBy('lead_id');
            });

        // --- 1. Today's Calls ---
        $todaysCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->whereIn('id', function ($query) use ($today) {
                $query->select('lead_id')
                    ->from('lead_details')
                    ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                            ->from('lead_details')
                            ->groupBy('lead_id');
                    })
                    ->whereDate('next_call_date', $today);
            })
            ->orderByDesc(
                LeadDetail::select('next_call_date')
                    ->whereColumn('lead_id', 'leads.id')
                    ->latest()
                    ->take(1)
            );

        // --- 2. Pending Calls ---
        $pendingCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->whereIn('id', function ($query) use ($today) {
                $query->select('lead_id')
                    ->from('lead_details')
                    ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                            ->from('lead_details')
                            ->groupBy('lead_id');
                    })
                    ->whereDate('next_call_date', '<', $today);
            })
            ->orderBy(
                LeadDetail::select('next_call_date')
                    ->whereColumn('lead_id', 'leads.id')
                    ->latest()
                    ->take(1)
            );

        // --- 3. Upcoming Calls ---
        $upcomingCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->whereIn('id', function ($query) use ($today) {
                $query->select('lead_id')
                    ->from('lead_details')
                    ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                            ->from('lead_details')
                            ->groupBy('lead_id');
                    })
                    ->whereDate('next_call_date', '>', $today);
            })
            ->orderBy(
                LeadDetail::select('next_call_date')
                    ->whereColumn('lead_id', 'leads.id')
                    ->latest()
                    ->take(1)
            );

        // --- Filter by user role ---
        if (!$user->isAdmin()) {
            $todaysCallsQuery->where('assigned_user_id', $user->id);
            $pendingCallsQuery->where('assigned_user_id', $user->id);
            $upcomingCallsQuery->where('assigned_user_id', $user->id);
        }

        // --- Execute queries ---
        $todaysCalls = $todaysCallsQuery->get();
        $pendingCalls = $pendingCallsQuery->get();
        $upcomingCalls = $upcomingCallsQuery->get();

        // --- Stats and other data ---
        $leads = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function ($q) {
            $q->latest();
        }])
        ->when(!$user->isAdmin(), fn($q) => $q->where('assigned_user_id', $user->id))
        ->orderBy('created_at', 'desc')
        ->get();

        $stats = [
            'total_leads' => $leads->count(),
            'todays_calls' => $todaysCalls->count(),
            'pending_calls' => $pendingCalls->count(),
            'upcoming_calls' => $upcomingCalls->count(),
            'leads_by_status' => $leads->groupBy('status.name')->map->count(),
        ];

        $users = $user->isAdmin() ? User::where('role', 'user')->get() : collect();
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
            ->whereHas('lead', function ($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // 2. Pending Call: next_call_date < today and called_at IS NULL
        $pendingCalls = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '<', $today)
            ->whereNull('called_at')
            ->whereHas('lead', function ($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // 3. Upcoming Call: next_call_date > today and called_at IS NULL
        $upcomingCalls = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser'])
            ->whereDate('next_call_date', '>', $today)
            ->whereNull('called_at')
            ->whereHas('lead', function ($query) use ($userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->orderBy('next_call_date', 'asc')
            ->get();

        // Get leads for selected user
        $leads = Lead::with(['service', 'status', 'assignedUser', 'leadDetails' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
            ->where('assigned_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ensure lead details are properly loaded and appended
        $leads->load(['leadDetails' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Explicitly append leadDetails to each lead for frontend
        $leads->each(function ($lead) {
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
