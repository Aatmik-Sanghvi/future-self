<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\Auth\GuestController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\GeneralController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\DailyMissionController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\MoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('V1')->group(function () {
    Route::post('register/send-otp', [GuestController::class, 'registerSendOtp']);
    Route::post('register/verify-otp', [GuestController::class, 'registerVerifyOtp']);
    Route::post('register/resend-otp', [GuestController::class, 'registerResendOtp']);
    Route::post('login',[GuestController::class, 'login']);
    Route::post('forgot-password', [GuestController::class, 'forgotPassword']);
    Route::post('verify-otp', [GuestController::class, 'verifyOtp']);
    Route::post('reset-password', [GuestController::class, 'resetPassword']);
    
    Route::get('stats', [GeneralController::class, 'stats']);
    // Social Auth routes
    Route::get('auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    
    Route::middleware(['auth:sanctum', 'log.activity'])->group(function () {
        Route::get('profile', [GeneralController::class, 'profile']);
        Route::post('update-profile',[GeneralController::class, 'updateProfile']);
        Route::post('update-password',[GeneralController::class, 'updatePassword']);
        Route::post('logout',[GeneralController::class, 'logout']);

        Route::post('delete-account', [GeneralController::class, 'deleteAccount']);

        // Feedback
        Route::post('feedback', [FeedbackController::class, 'store']);
        Route::get('feedback/status', [FeedbackController::class, 'status']);

        // Onboarding
        Route::prefix('onboarding')->group(function () {
            Route::post('goals', [OnboardingController::class, 'goals']);
            
            Route::post('fears', [OnboardingController::class, 'fears']);
            Route::post('struggle', [OnboardingController::class, 'struggles']);
            Route::post('desired-traits', [OnboardingController::class, 'desiredTraits']);
            Route::post('role-models', [OnboardingController::class, 'roleModels']);
            Route::post('tone', [OnboardingController::class, 'saveTone']);
            Route::get('onboarded', [OnboardingController::class, 'updateOnboarded']);

            Route::post('get-detail', [OnboardingController::class, 'getDetail']);
            Route::post('remove-detail', [OnboardingController::class, 'removeDetail']);
        });

        // Chat with FutureSelf agent
        Route::post('chat', [AIController::class, 'chat']);
        Route::get('conversations', [AIController::class, 'conversations']);
        Route::post('messages', [AIController::class, 'messages']);
        Route::post('delete-conversation', [AIController::class, 'deleteConversation']);

        // Daily moods checkin
        Route::post('daily-mood-checkin', [MoodController::class, 'mood']);

        // Daily missions
        Route::prefix('daily-mission')->group(function () {
            Route::get('today', [DailyMissionController::class, 'today']);
            Route::post('generate', [DailyMissionController::class, 'generate']);
            Route::post('{id}/complete', [DailyMissionController::class, 'complete']);
            Route::get('history', [DailyMissionController::class, 'history']);
            Route::get('reminder-settings', [DailyMissionController::class, 'getReminderSettings']);
            Route::post('reminder-settings', [DailyMissionController::class, 'updateReminderSettings']);
        });

        // Goal Tracking & Management
        Route::prefix('goals')->group(function () {
            Route::get('tracking', [GoalController::class, 'tracking']);
            Route::get('/', [GoalController::class, 'index']);
            Route::post('/', [GoalController::class, 'store']);
            Route::get('{id}', [GoalController::class, 'show']);
            Route::put('{id}', [GoalController::class, 'update']);
            Route::delete('{id}', [GoalController::class, 'destroy']);
            Route::post('{id}/progress', [GoalController::class, 'updateProgress']);
        });
    });
});

