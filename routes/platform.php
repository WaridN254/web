<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Platform\PlatformAuthController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformBusinessController;
use App\Http\Controllers\Platform\PlatformSubscriptionController;
use App\Http\Controllers\Platform\PlatformPlanController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\PlatformAuditLogController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\PlatformImpersonationController;

// Public platform routes
Route::prefix('platform')->name('platform.')->group(function () {
    Route::get('/login', [PlatformAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PlatformAuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [PlatformAuthController::class, 'logout'])->name('logout');
});

// Protected platform routes
Route::prefix('platform')
    ->middleware(['auth:platform', 'platform.auth'])
    ->name('platform.')
    ->group(function () {

        // Dashboard
        Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');

        // Businesses
        Route::get('/businesses', [PlatformBusinessController::class, 'index'])->name('businesses.index');
        Route::post('/businesses', [PlatformBusinessController::class, 'store'])->name('businesses.store');
        Route::get('/businesses/{id}', [PlatformBusinessController::class, 'show'])->name('businesses.show');
        Route::put('/businesses/{id}', [PlatformBusinessController::class, 'update'])->name('businesses.update');
        Route::post('/businesses/{id}/suspend', [PlatformBusinessController::class, 'suspend'])->name('businesses.suspend');
        Route::post('/businesses/{id}/activate', [PlatformBusinessController::class, 'activate'])->name('businesses.activate');
        Route::delete('/businesses/{id}', [PlatformBusinessController::class, 'destroy'])->name('businesses.destroy');

        // Subscriptions
        Route::get('/subscriptions', [PlatformSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [PlatformSubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::post('/subscriptions/{tenantId}/change-plan', [PlatformSubscriptionController::class, 'changePlan'])->name('subscriptions.change-plan');
        Route::post('/subscriptions/{tenantId}/cancel', [PlatformSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

        // Plans
        Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
        Route::post('/plans', [PlatformPlanController::class, 'store'])->name('plans.store');
        Route::put('/plans/{id}', [PlatformPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{id}', [PlatformPlanController::class, 'destroy'])->name('plans.destroy');

        // Platform Users
        Route::get('/platform-users', [PlatformUserController::class, 'index'])->name('platform-users.index');
        Route::post('/platform-users', [PlatformUserController::class, 'store'])->name('platform-users.store');
        Route::put('/platform-users/{id}', [PlatformUserController::class, 'update'])->name('platform-users.update');
        Route::post('/platform-users/{id}/reset-password', [PlatformUserController::class, 'resetPassword'])->name('platform-users.reset-password');
        Route::delete('/platform-users/{id}', [PlatformUserController::class, 'destroy'])->name('platform-users.destroy');

        // Audit Logs
        Route::get('/audit-logs', [PlatformAuditLogController::class, 'index'])->name('audit-logs.index');

        // Email Logs
        Route::get('/email-logs', [\App\Http\Controllers\Platform\PlatformEmailLogController::class, 'index'])->name('email-logs.index');
        Route::post('/email-logs/test', [\App\Http\Controllers\Platform\PlatformEmailLogController::class, 'sendTest'])->name('email-logs.test');

        // Settings
        Route::get('/settings', [PlatformSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [PlatformSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/restart-queue', [PlatformSettingController::class, 'restartQueue'])->name('settings.restart-queue');

        // Impersonation
        Route::post('/impersonate/{tenantId}/{userId}', [PlatformImpersonationController::class, 'start'])->name('impersonate.start');
        Route::post('/stop-impersonation', [PlatformImpersonationController::class, 'stop'])->name('impersonate.stop');
    });
