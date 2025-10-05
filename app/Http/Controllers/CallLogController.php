<?php

namespace App\Http\Controllers;

use App\Models\LeadDetail;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CallLogController extends Controller
{
    /**
     * Display a listing of call logs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get call logs with pagination
        $callLogsQuery = LeadDetail::with(['lead.service', 'lead.status', 'lead.assignedUser', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by user if not admin
        if (!$user->isAdmin()) {
            $callLogsQuery->whereHas('lead', function($query) use ($user) {
                $query->where('assigned_user_id', $user->id);
            });
        }

        $callLogs = $callLogsQuery->paginate(12);

        return Inertia::render('CallLog/Index', [
            'callLogs' => $callLogs,
            'user' => $user,
        ]);
    }
}
