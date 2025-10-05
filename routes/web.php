<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadDetailController;
use App\Http\Controllers\CallLogController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Test route to check if the issue is with the User Edit page
Route::get('/test-simple', function () {
    return \Inertia\Inertia::render('Users/Edit', [
        'user' => (object) [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'permissions' => []
        ],
        'permissions' => [
            'dashboard' => [(object) ['id' => 1, 'module' => 'dashboard', 'action' => 'view']],
            'leads' => [(object) ['id' => 2, 'module' => 'leads', 'action' => 'view']],
        ]
    ]);
})->name('test.simple');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/user/{user}', [DashboardController::class, 'filterByUser'])->middleware(['auth', 'verified'])->name('dashboard.filter');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lead routes with proper permission middleware
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index')->middleware('permission:leads,view');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store')->middleware('permission:leads,create');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show')->middleware('permission:leads,view');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit')->middleware('permission:leads,edit');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update')->middleware('permission:leads,edit');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy')->middleware('permission:leads,delete');

    // Call Log routes
    Route::get('call-logs', [CallLogController::class, 'index'])->name('call-logs.index')->middleware('permission:leads,view');

    // Lead detail routes
    Route::get('lead-details/{leadDetail}', [LeadDetailController::class, 'show'])->name('lead-details.show')->middleware('permission:leads,view');
    Route::get('lead-details/{leadDetail}/edit', [LeadDetailController::class, 'edit'])->name('lead-details.edit')->middleware('permission:leads,edit');
    Route::post('lead-details', [LeadDetailController::class, 'store'])->name('lead-details.store')->middleware('permission:leads,create');
    Route::put('lead-details/{leadDetail}', [LeadDetailController::class, 'update'])->name('lead-details.update')->middleware('permission:leads,edit');
    Route::patch('lead-details/{leadDetail}/complete', [LeadDetailController::class, 'markAsCompleted'])->name('lead-details.complete')->middleware('permission:leads,edit');
    Route::delete('lead-details/{leadDetail}', [LeadDetailController::class, 'destroy'])->name('lead-details.destroy')->middleware('permission:leads,delete');

    // Call log routes
    Route::get('call-log', [CallLogController::class, 'index'])->name('call-log.index')->middleware('permission:lead_details,view');

    // Status routes with proper permission middleware
    Route::get('statuses', [StatusController::class, 'index'])->name('statuses.index')->middleware('permission:statuses,view');
    Route::get('statuses/create', [StatusController::class, 'create'])->name('statuses.create')->middleware('permission:statuses,create');
    Route::post('statuses', [StatusController::class, 'store'])->name('statuses.store')->middleware('permission:statuses,create');
    Route::get('statuses/{status}', [StatusController::class, 'show'])->name('statuses.show')->middleware('permission:statuses,view');
    Route::get('statuses/{status}/edit', [StatusController::class, 'edit'])->name('statuses.edit')->middleware('permission:statuses,edit');
    Route::put('statuses/{status}', [StatusController::class, 'update'])->name('statuses.update')->middleware('permission:statuses,edit');
    Route::delete('statuses/{status}', [StatusController::class, 'destroy'])->name('statuses.destroy')->middleware('permission:statuses,delete');

    // Service routes with proper permission middleware
    Route::get('services', [ServiceController::class, 'index'])->name('services.index')->middleware('permission:services,view');
    Route::get('services/create', [ServiceController::class, 'create'])->name('services.create')->middleware('permission:services,create');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store')->middleware('permission:services,create');
    Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show')->middleware('permission:services,view');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit')->middleware('permission:services,edit');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update')->middleware('permission:services,edit');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy')->middleware('permission:services,delete');

    // User routes with proper permission middleware
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users,view');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users,create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users,create');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:users,view');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users,edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users,edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users,delete');

    // Search routes
    Route::get('api/search', [SearchController::class, 'search'])->name('search');
    Route::get('api/quick-search', [SearchController::class, 'quickSearch'])->name('quick-search');

    // API route for lead details
    Route::get('api/leads/{lead}/details', [LeadController::class, 'getLeadDetails'])->name('api.leads.details');

    // Simple API route for lead details (no auth required for testing)
    Route::get('api/leads/{lead}/call-history', function($leadId) {
        $lead = \App\Models\Lead::find($leadId);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found'], 404);
        }

        $leadDetails = $lead->leadDetails()->orderBy('created_at', 'desc')->get();
        return response()->json(['lead_details' => $leadDetails]);
    });
});

// Public API routes (outside auth middleware)
Route::get('api/leads/{lead}/call-history-public', function($leadId) {
    $lead = \App\Models\Lead::find($leadId);
    if (!$lead) {
        return response()->json(['error' => 'Lead not found'], 404);
    }

    $leadDetails = $lead->leadDetails()->orderBy('created_at', 'desc')->get();
    return response()->json(['lead_details' => $leadDetails]);
});

require __DIR__.'/auth.php';
