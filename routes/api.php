<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Route parameter constant
if (!defined('RESOURCE_ID')) {
    define('RESOURCE_ID', '/{id}');
}

// Swagger Documentation Routes
Route::get('/docs/json', function () {
    $jsonPath = storage_path('api-docs/api-docs.json');
    if (File::exists($jsonPath)) {
        return response(File::get($jsonPath), 200)
            ->header('Content-Type', 'application/json');
    }
    return response()->json(['error' => 'Swagger documentation not found'], 404);
});

// Swagger UI - Main documentation route
Route::get('/documentation', function () {
    return view('docs');
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Auth Routes (Public) - with rate limiting for security
Route::prefix('auth')->group(function () {
    // Rate limit: 5 login attempts per minute
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:5,1');

    Route::post('/refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])
        ->name('auth.refresh')
        ->middleware('throttle:10,1');

    // Rate limit: 3 password reset requests per minute
    Route::post('/forgot-password', [\App\Http\Controllers\AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password')
        ->middleware('throttle:3,1');

    Route::post('/reset-password', [\App\Http\Controllers\AuthController::class, 'resetPassword'])
        ->name('auth.reset-password')
        ->middleware('throttle:5,1');
});

// Protected Routes (Require Authentication)
Route::middleware(['auth.api', 'referrer.policy'])->group(function () {
    // Auth - Logout
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout']);

    // Auth - Register (Super Admin only)
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register'])
        ->middleware(['auth.api', 'role:super_admin']);

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Settings
    Route::prefix('settings')->group(function () {
        // Profile
        Route::get('/profile', [\App\Http\Controllers\SettingsController::class, 'getProfile']);
        Route::patch('/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile']);

        // Password
        Route::post('/change-password', [\App\Http\Controllers\SettingsController::class, 'changePassword']);
    });

    // User Management (Super Admin only)
    Route::prefix('users')->middleware('role:super_admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\UserController::class, 'store']);
        Route::get(RESOURCE_ID, [\App\Http\Controllers\UserController::class, 'show']);
        Route::put(RESOURCE_ID, [\App\Http\Controllers\UserController::class, 'update']);
        Route::delete(RESOURCE_ID, [\App\Http\Controllers\UserController::class, 'destroy']);
        Route::post(RESOURCE_ID.'/restore', [\App\Http\Controllers\UserController::class, 'restore']);
    });

    // Get available merchant owners (Admin and Super Admin)
    Route::get('/merchant-owners', [\App\Http\Controllers\UserController::class, 'getMerchantUsers'])
        ->middleware(['auth.api', 'role:super_admin|admin']);

    // Merchant Management
    Route::prefix('merchants')->group(function () {
        Route::get('/', [\App\Http\Controllers\MerchantController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\MerchantController::class, 'store'])->middleware('role:super_admin|admin');
        Route::get(RESOURCE_ID, [\App\Http\Controllers\MerchantController::class, 'show']);
        Route::put(RESOURCE_ID, [\App\Http\Controllers\MerchantController::class, 'update']);
        Route::delete(RESOURCE_ID, [\App\Http\Controllers\MerchantController::class, 'destroy'])->middleware('role:super_admin|admin');
    });
});
