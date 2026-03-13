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

class ReportController extends Controller
{
    /**
     * Display the index page for reports with summary stats.
     */
    public function index()
    {
        $user = auth()->user();
        return Inertia::render('Reports/Index', [
            'services' => Service::all(),
            'statuses' => Status::where('type', 'lead')->get(),
            'users' => $user->isAdmin() ? User::all() : [$user],
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
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Reports/LeadReport', [
            'leads' => $leads,
            'filters' => $request->all(),
            'services' => Service::all(),
            'statuses' => Status::where('type', 'lead')->get(),
            'users' => $user->isAdmin() ? User::all() : [$user],
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
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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
        $query = Lead::query()
            ->select('assigned_user_id', 'status_id', \DB::raw('count(*) as total'))
            ->groupBy('assigned_user_id', 'status_id')
            ->with(['assignedUser:id,name', 'status:id,name']);

        if (!$user->isAdmin()) {
            $query->where('assigned_user_id', $user->id);
        } else if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->assigned_user_id);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $results = $query->get();

        // Format data for easier table rendering
        $users = $user->isAdmin() ? User::all(['id', 'name']) : User::where('id', $user->id)->get(['id', 'name']);
        $statuses = Status::where('type', 'lead')->get(['id', 'name']);

        return Inertia::render('Reports/UserSummaryReport', [
            'results' => $results,
            'users' => $users,
            'statuses' => $statuses,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Printable Lead Report
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
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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
        $query = Lead::query()
            ->select('assigned_user_id', 'status_id', \DB::raw('count(*) as total'))
            ->groupBy('assigned_user_id', 'status_id')
            ->with(['assignedUser:id,name', 'status:id,name']);

        if (!$user->isAdmin()) {
            $query->where('assigned_user_id', $user->id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $results = $query->get();
        $users = $user->isAdmin() ? User::all(['id', 'name']) : User::where('id', $user->id)->get(['id', 'name']);
        $statuses = Status::where('type', 'lead')->get(['id', 'name']);

        return Inertia::render('Reports/Print/UserSummaryReportPrint', [
            'results' => $results,
            'users' => $users,
            'statuses' => $statuses,
            'filters' => $request->all(),
            'printTime' => now()->format('F j, Y - g:i A'),
        ]);
    }
}
