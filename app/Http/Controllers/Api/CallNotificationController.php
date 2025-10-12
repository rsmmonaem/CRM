<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CallNotificationController extends Controller
{
    /**
     * Send call notification to Android app
     */
    public function notifyAndroidCall(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'lead_id' => 'required|integer',
            'phone_number' => 'required|string',
            'call_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Store call notification in cache for Android app to pick up
        $notificationKey = "call_notification_{$request->user_id}_{$request->call_id}";
        $notificationData = [
            'user_id' => $request->user_id,
            'lead_id' => $request->lead_id,
            'phone_number' => $request->phone_number,
            'call_id' => $request->call_id,
            'timestamp' => now()->toISOString(),
            'from_web' => true,
        ];

        // Store for 5 minutes
        Cache::put($notificationKey, $notificationData, 300);

        // Also store in a user-specific queue
        $userQueueKey = "user_call_queue_{$request->user_id}";
        $userQueue = Cache::get($userQueueKey, []);
        $userQueue[] = $notificationData;

        // Keep only last 10 notifications
        if (count($userQueue) > 10) {
            $userQueue = array_slice($userQueue, -10);
        }

        Cache::put($userQueueKey, $userQueue, 300);

        Log::info('Call notification sent to Android app', [
            'user_id' => $request->user_id,
            'call_id' => $request->call_id,
            'phone_number' => $request->phone_number
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call notification sent to Android app',
            'data' => $notificationData
        ]);
    }

    /**
     * Get pending call notifications for Android app
     */
    public function getPendingNotifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $userQueueKey = "user_call_queue_{$user->id}";
        $notifications = Cache::get($userQueueKey, []);

        // Clear the queue after reading
        Cache::forget($userQueueKey);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'count' => count($notifications)
            ]
        ]);
    }

    /**
     * Mark notification as processed
     */
    public function markNotificationProcessed(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'call_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $notificationKey = "call_notification_{$user->id}_{$request->call_id}";

        Cache::forget($notificationKey);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as processed'
        ]);
    }
}
