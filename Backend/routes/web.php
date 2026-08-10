<?php

use App\Http\Controllers\Admin\Admin2FAController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Guest admin routes
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

    // Admin Google OAuth routes
    Route::get('auth/google', [AdminAuthController::class, 'redirectToGoogle'])->name('admin.auth.google');
    Route::get('auth/google/callback', [AdminAuthController::class, 'handleGoogleCallback'])->name('admin.auth.google.callback');

    // Protected admin routes (enforces admin privilege + 2FA)
    Route::middleware(['admin'])->group(function () {
        // 2FA management & challenge
        Route::get('2fa/setup', [Admin2FAController::class, 'showSetup'])->name('admin.2fa.setup');
        Route::post('2fa/setup', [Admin2FAController::class, 'confirmSetup'])->name('admin.2fa.confirm');
        Route::get('2fa/challenge', [Admin2FAController::class, 'showChallenge'])->name('admin.2fa.challenge');
        Route::post('2fa/challenge', [Admin2FAController::class, 'verifyChallenge'])->name('admin.2fa.verify');

        Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('activity-logs', [AdminDashboardController::class, 'activityLogs'])->name('admin.activity-logs');
        Route::get('daily-active-users', [AdminDashboardController::class, 'dailyActiveUsers'])->name('admin.daily-active-users');
    });
});