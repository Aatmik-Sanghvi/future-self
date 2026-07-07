<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\Auth\GuestController;
use App\Http\Controllers\Api\V1\GeneralController;
use App\Http\Controllers\Api\V1\OnboardingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('V1')->group(function () {
    Route::post('register', [GuestController::class, 'register']);
    Route::post('login',[GuestController::class, 'login']);
    Route::post('forgot-password', [GuestController::class, 'forgotPassword']);
    Route::post('verify-otp', [GuestController::class, 'verifyOtp']);
    Route::post('reset-password', [GuestController::class, 'resetPassword']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('profile', [GeneralController::class, 'profile']);
        Route::post('logout',[GeneralController::class, 'logout']);

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
    });
});
