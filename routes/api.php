<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallTrackingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User info
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'role' => $request->user()->role,
                    ]
                ]
            ]);
        });

        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Call Notification API Routes (for mobile app with Sanctum auth)
        Route::get('call-notifications/pending', [CallNotificationController::class, 'getPendingNotifications']);
        Route::post('call-notifications/processed', [CallNotificationController::class, 'markNotificationProcessed']);
    });

    // Call Tracking API Routes (for web app with session auth)
    Route::middleware(['auth', 'web'])->group(function () {
        Route::apiResource('call-trackings', CallTrackingController::class);
        Route::get('call-trackings/call/{callId}', [CallTrackingController::class, 'getByCallId']);
        Route::get('call-trackings/active', [CallTrackingController::class, 'getActiveCalls']);
        Route::get('call-trackings/stats', [CallTrackingController::class, 'getStats']);

        // Call Notification API Routes (for web app)
        Route::post('call-notifications/notify', [CallNotificationController::class, 'notifyAndroidCall']);
    });
