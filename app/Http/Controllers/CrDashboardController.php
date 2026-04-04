<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\LeadDetail;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;

class CrDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $filterUserId = $request->input('user_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Apply filters locally in queries
        $leadQuery = Lead::query();
        $leadDetailQuery = LeadDetail::query();
        
        if ($filterUserId && $user->isAdmin()) {
            $leadQuery->where('assigned_user_id', $filterUserId);
            $leadDetailQuery->whereHas('lead', function($q) use ($filterUserId) {
                $q->where('assigned_user_id', $filterUserId);
            });
        } elseif (!$user->isAdmin()) {
            $leadQuery->where('assigned_user_id', $user->id);
            $leadDetailQuery->whereHas('lead', function($q) use ($user) {
                $q->where('assigned_user_id', $user->id);
            });
        }
        $totalAssignLead = clone $leadQuery;// for intotal no date will filter 
        $intotalLeadDetail = clone $leadDetailQuery; // for intotal calls no date filter 
        if ($fromDate && $toDate) {
            $leadQuery->whereBetween('created_at', [$fromDate, Carbon::parse($toDate)->endOfDay()]);
            $leadDetailQuery->whereBetween('created_at', [$fromDate, Carbon::parse($toDate)->endOfDay()]);
        }

        // 1. Core Base Queries for Metrics
        // Here we use whereHas to only count the leads that match the condition for the latest activity
        $today = Carbon::today();
        
        
        
        // Unique leads that have been called at least once
        $uniqueLeadsCalledCount = (clone $leadQuery)->whereHas('leadDetails')->count();

        // Status 11 = "Office Visited" (based on your leads app structure)
        $visitedLeadsQuery = clone $leadQuery;

        $totalAssignLeadCount = $totalAssignLead->count();
        $todaytotalAssignLeadCount = (clone $leadQuery)->whereDate('created_at', $today)->count();
        
        // Today Total Call (number of calls made from date and to date same )
        if ($fromDate && $toDate) {
            $todayTotalCallCount = (clone $leadDetailQuery)->count();
        } else {
            $todayTotalCallCount = (clone $leadDetailQuery)->whereDate('created_at', $today)->count();
        }
        
        // Intotal Call (All Lead Details)
        $intotalCallCount = $intotalLeadDetail->count();
        
        $repeatCallCount = max(0, $intotalCallCount - $uniqueLeadsCalledCount);

        // Pending Call: The NEXT call date has PASSED (before today)
        $pendingQuery = (clone $leadQuery)->whereHas('leadDetails', function($q) use ($today) {
            $q->whereDate('next_call_date', '<', $today);
        });
        $pendingFollowupCount = $pendingQuery->count();
        $intotalPendingCallCount = (clone $totalAssignLead)->doesntHave('leadDetails')->count();
        $todayPendingCallCount = (clone $leadQuery)->whereDate('created_at', $today)->doesntHave('leadDetails')->count();

        // Today Followup (Today's Call): The NEXT call date is TODAY
        $todayFollowupQuery = (clone $leadQuery)->whereHas('leadDetails', function($q) use ($today) {
            $q->whereDate('next_call_date', $today);
        });
        $todayFollowupCount = $todayFollowupQuery->count();

        // Upcoming Calls: The NEXT call date is IN THE FUTURE (after today)
        $upcomingCallsQuery = (clone $leadQuery)->whereHas('leadDetails', function($q) use ($today) {
            $q->whereDate('next_call_date', '>', $today);
        });
        $upcomingCallsCount = $upcomingCallsQuery->count();

        // 2. CRM Lead Call Result Summary (Based on `call_status` string in LeadDetail)
        $callStatuses = Status::where('type', 'call')->get();
        $callResultSummary = [];
        $colorClasses = ['green', 'cyan', 'yellow', 'red', 'blue', 'orange', 'purple', 'darkgreen', 'gray', 'pink'];
        
        foreach ($callStatuses as $index => $status) {
            // Count details where the stored string name matches
            $matches = (clone $leadDetailQuery)->where('call_status', $status->name)->count();
            if ($matches > 0) {
                $callResultSummary[] = [
                    'name' => $status->name,
                    'count' => $matches,
                    'color' => $colorClasses[$index % count($colorClasses)]
                ];
            }
        }

        // 3. Reach Call Details (From Lead Status - `status_id` column in Leads table)
        $leadStatuses = Status::where('type', 'lead')->get();
        $leadResultSummary = [];
        
        foreach ($leadStatuses as $index => $status) {
            $matches = (clone $leadQuery)->where('status_id', $status->id)->count();
            if ($matches > 0) {
                $leadResultSummary[] = [
                    'name' => $status->name,
                    'count' => $matches,
                    'color' => $colorClasses[($index + 4) % count($colorClasses)]
                ];
            }
        }

        // 4. Follow Up Details
        // Let's assume Office Visited is status 11
        $todayVisitCount = (clone $visitedLeadsQuery)->whereDate('updated_at', $today)->where('status_id', 11)->count();
        $totalVisitCount = (clone $visitedLeadsQuery)->where('status_id', 11)->count();

        $users = User::select('id', 'name')->get();

        return Inertia::render('CrDashboard', [
            'metrics' => [
                'intotalAssignLead' => $totalAssignLeadCount,
                'intotalPendingCall' => $intotalPendingCallCount,
                'todaytotalAssignLead' => $todaytotalAssignLeadCount,
                'todayPendingCall' => $todayPendingCallCount,
                'todayTotalCall' => $todayTotalCallCount,
                'intotalCall' => $intotalCallCount,
                'repeatCall' => $repeatCallCount,
                'todayFollowup' => $todayFollowupCount,
                'pendingFollowup' => $pendingFollowupCount,
                'upcomingCalls' => $upcomingCallsCount,
                'todayVisit' => $todayVisitCount,
                'totalVisit' => $totalVisitCount,
            ],
            'callResultSummary' => $callResultSummary,
            'leadResultSummary' => $leadResultSummary,
            'users' => $users,
            'filters' => [
                'user_id' => $filterUserId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'user' => $user
        ]);
    }
}
