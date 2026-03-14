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
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        // 1. Static/Lookup Data (Cached for 1 hour)
        $services = Cache::remember('services_list', 3600, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 3600, fn() => Status::where('type', 'lead')->get());
        $call_statuses = Cache::remember('call_statuses_list', 3600, fn() => Status::where('type', 'call')->get());
        $modalUsers = Cache::remember('users_list', 3600, fn() => User::where('role', 'user')->select('id', 'name')->get());

        // 2. Optimized Dashboard Queries using latestLeadDetail
        $todaysCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'latestLeadDetail'])
            ->whereHas('latestLeadDetail', function ($q) use ($today) {
                $q->whereDate('next_call_date', $today);
            });

        $pendingCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'latestLeadDetail'])
            ->whereHas('latestLeadDetail', function ($q) use ($today) {
                $q->whereDate('next_call_date', '<', $today);
            });

        $upcomingCallsQuery = Lead::with(['service', 'status', 'assignedUser', 'latestLeadDetail'])
            ->whereHas('latestLeadDetail', function ($q) use ($today) {
                $q->whereDate('next_call_date', '>', $today);
            });

        if (!$user->isAdmin()) {
            $todaysCallsQuery->where('assigned_user_id', $user->id);
            $pendingCallsQuery->where('assigned_user_id', $user->id);
            $upcomingCallsQuery->where('assigned_user_id', $user->id);
        }

        $todaysCalls = $todaysCallsQuery->orderBy(
            LeadDetail::select('next_call_date')
                ->whereColumn('lead_id', 'leads.id')
                ->latest()
                ->take(1),
            'desc'
        )->get();

        $pendingCalls = $pendingCallsQuery->orderBy(
            LeadDetail::select('next_call_date')
                ->whereColumn('lead_id', 'leads.id')
                ->latest()
                ->take(1)
        )->get();

        $upcomingCalls = $upcomingCallsQuery->orderBy(
            LeadDetail::select('next_call_date')
                ->whereColumn('lead_id', 'leads.id')
                ->latest()
                ->take(1)
        )->get();

        // 3. Stats (Cached for 5 minutes per user)
        $statsCacheKey = 'dashboard_stats_' . $user->id;
        $stats = Cache::remember($statsCacheKey, 300, function() use ($user, $todaysCalls, $pendingCalls, $upcomingCalls) {
            $leadsQuery = Lead::when(!$user->isAdmin(), fn($q) => $q->where('assigned_user_id', $user->id));
            
            return [
                'total_leads' => $leadsQuery->count(),
                'todays_calls' => $todaysCalls->count(),
                'pending_calls' => $pendingCalls->count(),
                'upcoming_calls' => $upcomingCalls->count(),
                'leads_by_status' => Lead::join('statuses', 'leads.status_id', '=', 'statuses.id')
                    ->when(!$user->isAdmin(), fn($q) => $q->where('assigned_user_id', $user->id))
                    ->groupBy('statuses.name')
                    ->selectRaw('statuses.name, count(*) as count')
                    ->pluck('count', 'name'),
            ];
        });

        // 4. Recently created leads (Limited to 20 for performance)
        $recentLeads = Lead::with(['service', 'status', 'assignedUser', 'latestLeadDetail'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('assigned_user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Dashboard', [
            'todaysCalls' => $todaysCalls,
            'pendingCalls' => $pendingCalls,
            'upcomingCalls' => $upcomingCalls,
            'leads' => $recentLeads,
            'stats' => $stats,
            'users' => $modalUsers,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'call_statuses' => $call_statuses,
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
            'call_statuses' => Status::where('type', 'call')->get(),
            'modalUsers' => $modalUsers,
        ]);
    }
}
