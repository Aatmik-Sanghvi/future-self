<?php

namespace App\Providers;

use App\Ai\Services\GeminiRateLimiter;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Events\AgentFailedOver;
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payments\RazorpayGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       $this->app->bind(
            PaymentGatewayInterface::class,
            RazorpayGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        Schema::defaultStringLength(191);

        Event::listen(AgentFailedOver::class, function (AgentFailedOver $event) {
            GeminiRateLimiter::markModelRateLimited($event->provider->name());
            $agentClass = class_basename($event->agent);
            Log::warning(
                "⚠️ [AI Failover] Agent [{$agentClass}] failed on provider [{$event->provider->name()}] (model: {$event->model}): ".
                "{$event->exception->getMessage()}. Automatically switching to next Gemini fallback model."
            );
        });

        // Restrict queued Gemini jobs to 14/min globally to prevent hitting 15 RPM cap
        RateLimiter::for('gemini-jobs', function (object $job) {
            $jobClass = class_basename($job);
            $key = md5('gemini-jobs'.'gemini-global-lock');
            $attempts = RateLimiter::attempts($key);
            $isLimited = RateLimiter::tooManyAttempts($key, 14);

            if ($isLimited) {
                $retryAfter = RateLimiter::availableIn($key);
                Log::warning(
                    "⏳ [Gemini Queue Limiter] Rate limit reached (14/14 per min). Job [{$jobClass}] will be delayed and released back to queue (available in {$retryAfter}s)."
                );
            } else {
                $slotsLeft = 14 - $attempts;
                Log::info(
                    "🚀 [Gemini Queue Limiter] Job [{$jobClass}] permitted to execute. Current minute usage: {$attempts}/14 ({$slotsLeft} slot(s) remaining)."
                );
            }

            return Limit::perMinute(14)->by('gemini-global-lock');
        });
    }
}
