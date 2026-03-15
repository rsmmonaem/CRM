<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use App\Models\LeadDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /**
     * Display the index page for reports with summary stats.
     */
    public function index()
    {
        $user = auth()->user();
        
        $services = Cache::remember('services_list', 3600, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 3600, fn() => Status::where('type', 'lead')->get());
        $users = Cache::remember('users_list', 3600, fn() => $user->isAdmin() ? User::all() : [$user]);

        return Inertia::render('Reports/Index', [
            'services' => $services,
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }

    /**
     * Generate lead report based on filters.
     */
    public function leadReport(Request $request)
    {
        $user = auth()->user();
        $query = Lead::with(['service', 'status', 'assignedUser', 'creator']);

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if (!$user->isAdmin()) {
            $query->where('assigned_user_id', $user->id);
        } else if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->assigned_user_id);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $services = Cache::remember('services_list', 3600, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 3600, fn() => Status::where('type', 'lead')->get());
        $users = Cache::remember('users_list', 3600, fn() => $user->isAdmin() ? User::all() : [$user]);

        return Inertia::render('Reports/LeadReport', [
            'leads' => $leads,
            'filters' => $request->all(),
            'services' => $services,
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }

    /**
     * Generate call log report based on filters.
     */
    public function callLogReport(Request $request)
    {
        $user = auth()->user();
        $query = LeadDetail::with(['lead.status', 'lead.service', 'creator']);

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->filled('service_id')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        if ($request->filled('status_id')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('status_id', $request->status_id);
            });
        }

        if (!$user->isAdmin()) {
            $query->whereHas('lead', function ($q) use ($user) {
                $q->where('assigned_user_id', $user->id);
            });
        } else if ($request->filled('assigned_user_id')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('assigned_user_id', $request->assigned_user_id);
            });
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        $callLogs = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Reports/CallLogReport', [
            'callLogs' => $callLogs,
            'filters' => $request->all(),
            'leads' => $user->isAdmin() ? Lead::select('id', 'name', 'phone')->get() : Lead::where('assigned_user_id', $user->id)->select('id', 'name', 'phone')->get(),
            'users' => $user->isAdmin() ? User::all() : [$user],
        ]);
    }

    /**
     * Generate user summary report (User Name, Status, Total)
     */
    public function userSummaryReport(Request $request)
    {
        $user = auth()->user();
        
        // 1. Caching lookup data
        $usersList = Cache::remember('summary_users_list_' . ($user->isAdmin() ? 'all' : $user->id), 3600, function() use ($user) {
            return $user->isAdmin() ? User::all(['id', 'name']) : User::where('id', $user->id)->get(['id', 'name']);
        });
        $statusesList = Cache::remember('call_statuses_list', 3600, function() {
            return Status::where('type', 'call')->get(['id', 'name']);
        });

        // 2. Building query
        $query = LeadDetail::query()
            ->join('leads', 'lead_details.lead_id', '=', 'leads.id');

        // Apply filters
        if (!$user->isAdmin()) {
            $query->where('lead_details.created_by', $user->id);
        } elseif ($request->filled('assigned_user_id')) {
            $query->where('lead_details.created_by', $request->assigned_user_id);
        }

        if ($request->filled('service_id')) {
            $query->where('leads.service_id', $request->service_id);
        }

        if ($request->filled('status_id')) {
            $query->where('leads.status_id', $request->status_id);
        }

        // Date Filters
        if ($request->filled('from_date')) {
            $query->where('lead_details.created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('lead_details.created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        // Aggregate results
        $results = $query->select('lead_details.created_by as assigned_user_id', 'lead_details.call_status', \DB::raw('count(*) as total'))
            ->groupBy('lead_details.created_by', 'lead_details.call_status')
            ->get();

        return Inertia::render('Reports/UserSummaryReport', [
            'results' => $results,
            'users' => $usersList,
            'statuses' => $statusesList,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Printable Lead Reportit
     */
    public function leadReportPrint(Request $request)
    {
        $user = auth()->user();
        $query = Lead::with(['service', 'status', 'assignedUser', 'creator']);

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        
        if (!$user->isAdmin()) {
            $query->where('assigned_user_id', $user->id);
        } else if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->assigned_user_id);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Reports/Print/LeadReportPrint', [
            'leads' => $leads,
            'filters' => $request->all(),
            'printTime' => now()->format('F j, Y - g:i A'),
        ]);
    }

    /**
     * Printable Call Log Report
     */
    public function callLogReportPrint(Request $request)
    {
        $user = auth()->user();
        $query = LeadDetail::with(['lead.status', 'lead.service', 'creator']);

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->filled('service_id')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        if ($request->filled('status_id')) {
            $query->whereHas('lead', function ($q) use ($request) {
                $q->where('status_id', $request->status_id);
            });
        }
        
        if (!$user->isAdmin()) {
            $query->where('created_by', $user->id);
        } else if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        $callLogs = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Reports/Print/CallLogReportPrint', [
            'callLogs' => $callLogs,
            'filters' => $request->all(),
            'printTime' => now()->format('F j, Y - g:i A'),
        ]);
    }

    /**
     * Printable User Summary Report
     */
    public function userSummaryReportPrint(Request $request)
    {
        $user = auth()->user();
        $query = LeadDetail::query()
            ->join('leads', 'lead_details.lead_id', '=', 'leads.id');

        if (!$user->isAdmin()) {
            $query->where('lead_details.created_by', $user->id);
        } elseif ($request->filled('assigned_user_id')) {
            $query->where('lead_details.created_by', $request->assigned_user_id);
        }

        if ($request->filled('service_id')) {
            $query->where('leads.service_id', $request->service_id);
        }

        if ($request->filled('status_id')) {
            $query->where('leads.status_id', $request->status_id);
        }

        if ($request->filled('from_date')) {
            $query->where('lead_details.created_at', '>=', \Carbon\Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->filled('to_date')) {
            $query->where('lead_details.created_at', '<=', \Carbon\Carbon::parse($request->to_date)->endOfDay());
        }

        $results = $query->select('lead_details.created_by as assigned_user_id', 'lead_details.call_status', \DB::raw('count(*) as total'))
            ->groupBy('lead_details.created_by', 'lead_details.call_status')
            ->get();

        $users = $user->isAdmin() ? User::all(['id', 'name']) : User::where('id', $user->id)->get(['id', 'name']);
        $statuses = Status::where('type', 'call')->get(['id', 'name']);

        return Inertia::render('Reports/Print/UserSummaryReportPrint', [
            'results' => $results,
            'users' => $users,
            'statuses' => $statuses,
            'filters' => $request->all(),
            'printTime' => now()->format('F j, Y - g:i A'),
        ]);
    }
}
