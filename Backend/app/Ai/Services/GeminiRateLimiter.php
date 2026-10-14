<?php

namespace App\Ai\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiRateLimiter
{
    /**
     * Ensure requests to Gemini API are strictly paced to avoid hitting the 15 RPM limit.
     *
     * @param  (callable(float $seconds, string $reason): void)|null  $feedbackCallback
     * @return float Total seconds waited
     */
    public static function ensurePacing(?callable $feedbackCallback = null): float
    {
        $minInterval = (float) config('ai.gemini_rate_limiting.min_interval_seconds', 4.2);
        $maxPerMinute = (int) config('ai.gemini_rate_limiting.max_per_minute', 14);

        $totalSlept = 0.0;

        $executePacing = function () use ($minInterval, $maxPerMinute, $feedbackCallback, &$totalSlept) {
            $now = microtime(true);
            $lastRequestTime = (float) Cache::get('gemini_last_request_time', 0.0);
            $recentRequests = Cache::get('gemini_recent_requests', []);

            if (! is_array($recentRequests)) {
                $recentRequests = [];
            }

            // 1. Purge timestamps older than 60 seconds
            $recentRequests = array_values(array_filter($recentRequests, fn ($t) => ($now - (float) $t) < 60.0));

            // 2. Rolling window check: ensure max requests in the last 60s is strictly below limit
            if (count($recentRequests) >= $maxPerMinute) {
                $oldestTimestamp = (float) $recentRequests[0];
                $windowWait = 60.0 - ($now - $oldestTimestamp) + 0.2; // 200ms safety buffer

                if ($windowWait > 0) {
                    if ($feedbackCallback) {
                        $feedbackCallback($windowWait, '60s rolling window cap (max 14/min)');
                    }
                    usleep((int) ($windowWait * 1_000_000));
                    $totalSlept += $windowWait;
                    $now = microtime(true);
                    $recentRequests = array_values(array_filter($recentRequests, fn ($t) => ($now - (float) $t) < 60.0));
                }
            }

            // 3. Minimum interval check: ensure at least 4.2 seconds between consecutive requests
            $elapsed = $now - $lastRequestTime;
            if ($elapsed < $minInterval && $lastRequestTime > 0) {
                $intervalWait = $minInterval - $elapsed;
                if ($intervalWait > 0) {
                    if ($feedbackCallback) {
                        $feedbackCallback($intervalWait, 'interval pacing (min 4.2s apart)');
                    }
                    usleep((int) ($intervalWait * 1_000_000));
                    $totalSlept += $intervalWait;
                }
            }

            // 4. Update tracking timestamps
            $finalTimestamp = microtime(true);
            $recentRequests[] = $finalTimestamp;
            Cache::put('gemini_recent_requests', $recentRequests, 120);
            Cache::put('gemini_last_request_time', $finalTimestamp, 120);
        };

        // Use cache lock if possible for cross-process synchronization
        try {
            $lock = Cache::lock('gemini_pacing_lock', 10);
            $lock->block(10, $executePacing);
        } catch (\Throwable $e) {
            // Fallback to unlocked execution if lock driver not supported or times out
            $executePacing();
        }

        return $totalSlept;
    }

    /**
     * Get the active Gemini provider chain, placing healthy models first and cooling-down models at the end.
     */
    public static function getFailoverChain(): array
    {
        $configuredChain = config('ai.gemini_rate_limiting.failover_chain', [
            'gemini',
            'gemini_flash_lite',
            'gemini_flash_lite_latest',
            'gemini_3_1_flash_lite',
            'gemini_flash_latest',
            'gemini_pro_latest',
        ]);

        $healthy = [];
        $coolingDown = [];

        foreach ($configuredChain as $provider) {
            if (Cache::has("gemini_cooldown_{$provider}")) {
                $coolingDown[] = $provider;
            } else {
                $healthy[] = $provider;
            }
        }

        // Return healthy models first, followed by cooling-down models as backup
        $result = array_merge($healthy, $coolingDown);

        return ! empty($result) ? $result : ['gemini'];
    }

    /**
     * Mark a Gemini provider/model as rate-limited or overloaded for a cooldown period.
     */
    public static function markModelRateLimited(string $providerName, ?int $cooldownSeconds = null): void
    {
        $cooldown = $cooldownSeconds ?? (int) config('ai.gemini_rate_limiting.cooldown_seconds', 60);

        Cache::put("gemini_cooldown_{$providerName}", now()->toISOString(), $cooldown);

        $nextChain = self::getFailoverChain();
        $nextProvider = $nextChain[0] ?? 'none';
        $nextModel = config("ai.providers.{$nextProvider}.models.text.default")
            ?? config("ai.providers.{$nextProvider}.model", $nextProvider);

        Log::warning(
            "Gemini provider [{$providerName}] hit rate limit or overload. Marked on cooldown for {$cooldown}s. ".
            "Active primary is now [{$nextProvider}] using model [{$nextModel}]."
        );
    }

    /**
     * Check if a specific Gemini provider is currently cooling down.
     */
    public static function isCoolingDown(string $providerName): bool
    {
        return Cache::has("gemini_cooldown_{$providerName}");
    }

    /**
     * Clear all cooldowns (resets all models to healthy status).
     */
    public static function clearCooldowns(): void
    {
        $chain = config('ai.gemini_rate_limiting.failover_chain', [
            'gemini',
            'gemini_flash_lite',
            'gemini_flash_lite_latest',
            'gemini_3_1_flash_lite',
            'gemini_flash_latest',
            'gemini_pro_latest',
        ]);

        foreach ($chain as $provider) {
            Cache::forget("gemini_cooldown_{$provider}");
        }

        Cache::forget('gemini_recent_requests');
        Cache::forget('gemini_last_request_time');
    }
}
