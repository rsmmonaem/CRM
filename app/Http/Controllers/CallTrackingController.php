<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CallTracking;
use App\Models\Lead;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CallTrackingController extends Controller
{
    /**
     * Display a listing of call trackings
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = CallTracking::with(['lead', 'user', 'leadDetail'])
            ->orderBy('created_at', 'desc');

        // Filter by user if not admin
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Filter by lead if provided
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('call_status', $request->status);
        }

        $callTrackings = $query->paginate(20);

        return response()->json($callTrackings);
    }

    /**
     * Initiate a new call tracking
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'phone_number' => 'required|string',
            'device_type' => 'in:web,android,ios',
            'device_id' => 'nullable|string',
            'is_auto_dialed' => 'boolean',
            'lead_detail_id' => 'nullable|exists:lead_details,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $lead = Lead::findOrFail($request->lead_id);

        // Check if user can access this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $callTracking = CallTracking::create([
            'lead_id' => $request->lead_id,
            'user_id' => $user->id,
            'lead_detail_id' => $request->lead_detail_id,
            'phone_number' => $request->phone_number,
            'call_id' => Str::uuid(),
            'device_type' => $request->device_type ?? 'web',
            'device_id' => $request->device_id,
            'is_auto_dialed' => $request->is_auto_dialed ?? false,
            'call_status' => 'initiated',
            'call_metadata' => $request->metadata ?? []
        ]);

        return response()->json([
            'message' => 'Call tracking initiated',
            'call_tracking' => $callTracking->load(['lead', 'user', 'leadDetail']),
            'call_id' => $callTracking->call_id
        ], 201);
    }

    /**
     * Display the specified call tracking
     */
    public function show($id)
    {
        $callTracking = CallTracking::with(['lead', 'user', 'leadDetail'])->findOrFail($id);

        $user = auth()->user();

        // Check if user can view this call tracking
        if (!$user->isAdmin() && $callTracking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($callTracking);
    }

    /**
     * Update call tracking status
     */
    public function update(Request $request, $id)
    {
        $callTracking = CallTracking::findOrFail($id);

        $user = auth()->user();

        // Check if user can update this call tracking
        if (!$user->isAdmin() && $callTracking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'call_status' => 'in:initiated,ringing,answered,completed,cancelled,failed,busy,no_answer',
            'call_summary' => 'nullable|string',
            'next_call_date' => 'nullable|date|after:today',
            'audio_recording' => 'nullable|file|mimes:mp3,wav,m4a|max:10240', // 10MB max
            'call_metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [];

        // Handle status updates
        if ($request->has('call_status')) {
            $status = $request->call_status;

            switch ($status) {
                case 'ringing':
                    $callTracking->markAsStarted();
                    break;
                case 'answered':
                    $callTracking->markAsAnswered();
                    break;
                case 'completed':
                    $callTracking->markAsCompleted($request->call_summary, $request->next_call_date);
                    break;
                case 'cancelled':
                case 'failed':
                case 'busy':
                case 'no_answer':
                    $callTracking->markAsCancelled();
                    $callTracking->update(['call_status' => $status]);
                    break;
                default:
                    $updateData['call_status'] = $status;
            }
        }

        // Handle audio recording upload
        if ($request->hasFile('audio_recording')) {
            $file = $request->file('audio_recording');
            $filename = 'call_' . $callTracking->call_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('call_recordings', $filename, 'public');
            $updateData['audio_recording_path'] = $path;
        }

        // Handle metadata updates
        if ($request->has('call_metadata')) {
            $updateData['call_metadata'] = array_merge(
                $callTracking->call_metadata ?? [],
                $request->call_metadata
            );
        }

        // Handle other updates
        if ($request->has('call_summary') && !$request->has('call_status')) {
            $updateData['call_summary'] = $request->call_summary;
        }

        if (!empty($updateData)) {
            $callTracking->update($updateData);
        }

        return response()->json([
            'message' => 'Call tracking updated',
            'call_tracking' => $callTracking->fresh(['lead', 'user', 'leadDetail'])
        ]);
    }

    /**
     * Get call tracking by call_id (for mobile apps)
     */
    public function getByCallId($callId)
    {
        $callTracking = CallTracking::with(['lead', 'user', 'leadDetail'])
            ->where('call_id', $callId)
            ->firstOrFail();

        $user = auth()->user();

        // Check if user can view this call tracking
        if (!$user->isAdmin() && $callTracking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($callTracking);
    }

    /**
     * Get active calls for user
     */
    public function getActiveCalls()
    {
        $user = auth()->user();

        $query = CallTracking::active()
            ->with(['lead', 'user', 'leadDetail'])
            ->orderBy('call_started_at', 'desc');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $activeCalls = $query->get();

        return response()->json($activeCalls);
    }

    /**
     * Get call statistics
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();

        $query = CallTracking::query();

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_calls' => $query->count(),
            'completed_calls' => $query->where('call_status', 'completed')->count(),
            'total_duration' => $query->where('call_status', 'completed')->sum('call_duration_seconds'),
            'average_duration' => $query->where('call_status', 'completed')->avg('call_duration_seconds'),
            'calls_by_status' => $query->groupBy('call_status')->selectRaw('call_status, count(*) as count')->get(),
            'calls_by_day' => $query->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Get call tracking history for a specific lead
     */
    public function getLeadCallHistory($leadId)
    {
        $user = auth()->user();
        $lead = Lead::findOrFail($leadId);

        // Check if user can access this lead
        if (!$user->isAdmin() && $lead->assigned_user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $callHistory = CallTracking::with(['user', 'leadDetail'])
            ->where('lead_id', $leadId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'lead' => $lead,
            'call_history' => $callHistory,
            'total_calls' => $callHistory->count(),
            'completed_calls' => $callHistory->where('call_status', 'completed')->count(),
            'total_duration' => $callHistory->where('call_status', 'completed')->sum('call_duration_seconds')
        ]);
    }

    /**
     * Remove the specified call tracking
     */
    public function destroy($id)
    {
        $callTracking = CallTracking::findOrFail($id);

        $user = auth()->user();

        // Check if user can delete this call tracking
        if (!$user->isAdmin() && $callTracking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete audio recording if exists
        if ($callTracking->audio_recording_path) {
            Storage::disk('public')->delete($callTracking->audio_recording_path);
        }

        $callTracking->delete();

        return response()->json(['message' => 'Call tracking deleted']);
    }
}
