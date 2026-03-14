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
        $services = Cache::remember('services_list', 3600, fn() => Service::all());
        $statuses = Cache::remember('lead_statuses_list', 3600, fn() => Status::where('type', 'lead')->get());
        $call_statuses = Cache::remember('call_statuses_list', 3600, fn() => Status::where('type', 'call')->get());
        $users = Cache::remember('users_list', 3600, fn() => User::select('id', 'name')->get());

        // 2. Optimized Call Logs Query (Limited to 500 for client-side filtering performance)
        $callLogsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(500);

        if (!$user->isAdmin()) {
            $callLogsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });
        }

        $callLogs = $callLogsQuery->get();

        return Inertia::render('CallLog/Index', [
            'callLogs' => $callLogs,
            'user' => $user,
            'services' => $services,
            'statuses' => $statuses,
            'call_statuses' => $call_statuses,
            'users' => $users,
        ]);
    }
}
