<?php

namespace App\Http\Controllers;

use App\Models\LeadDetail;
use App\Models\Lead;
use App\Models\User;
use App\Models\Service;
use App\Models\Status;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class CallLogController extends Controller
{
    /**
     * Display a listing of call logs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Static/Lookup Data (Cached)
        $services = Cache::remember('services_list', 86400, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 86400, fn() => Status::where('type', 'lead')->get());
        $call_statuses = Cache::remember('call_statuses_list', 86400, fn() => Status::where('type', 'call')->get());
        $users = Cache::remember('users_list', 86400, fn() => User::select('id', 'name')->get());

        // 2. Optimized Call Logs Query
        $callLogsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser', 'creator']);

        if (!$user->isAdmin()) {
            $callLogsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $callLogsQuery->where(function($q) use ($search) {
                $q->whereHas('lead', function($lq) use ($search) {
                    $lq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('company_name', 'like', "%{$search}%");
                })->orWhere('call_followup_summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service')) {
            $callLogsQuery->whereHas('lead', function($q) use ($request) {
                $q->where('service_id', $request->input('service'));
            });
        }

        if ($request->filled('status')) {
            $callLogsQuery->whereHas('lead', function($q) use ($request) {
                $q->where('status_id', $request->input('status'));
            });
        }

        if ($request->filled('call_status')) {
            $callLogsQuery->where('call_status', $request->input('call_status'));
        }

        if ($request->filled('dateFrom')) {
            $callLogsQuery->whereDate('call_followup_date', '>=', $request->input('dateFrom'));
        }

        if ($request->filled('dateTo')) {
            $callLogsQuery->whereDate('call_followup_date', '<=', $request->input('dateTo'));
        }

        if ($user->isAdmin() && $request->filled('user')) {
            $callLogsQuery->whereHas('lead', function($q) use ($request) {
                $q->where('assigned_user_id', $request->input('user'));
            });
        }

        if ($user->isAdmin() && $request->filled('log_by')) {
            $callLogsQuery->where('created_by', $request->input('log_by'));
        }

        $totalLogs = $callLogsQuery->count();
        $perPageInput = $request->input('per_page', 50);
        
        if ($perPageInput === 'all') {
            $perPage = $totalLogs > 0 ? min($totalLogs, 1000) : 50;
        } else {
            $perPage = is_numeric($perPageInput) ? (int)$perPageInput : 50;
            if ($perPage <= 0) $perPage = 50;
        }

        $callLogs = $callLogsQuery->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        return Inertia::render('CallLog/Index', [
            'callLogs' => $callLogs,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'call_statuses' => $call_statuses,
            'users' => $users,
            'filters' => $request->only(['search', 'service', 'status', 'call_status', 'dateFrom', 'dateTo', 'user', 'log_by', 'per_page']),
        ]);
    }
}
