<?php

namespace App\Ai\Concerns;

use App\Ai\Services\GeminiRateLimiter;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Providers\Provider;
use Laravel\Ai\Responses\AgentResponse;

trait HasGeminiFailover
{
    /**
     * Get the configured Gemini providers list for failover.
     * Overrides Promptable::provider() to provide ordered Gemini model switching.
     */
    public function provider(): array
    {
        return GeminiRateLimiter::getFailoverChain();
    }

    /**
     * Invoke the agent with explicit pacing to guarantee the 15 RPM limit is respected.
     */
    public function promptPaced(
        string $prompt,
        array $attachments = [],
        Lab|array|string|null $provider = null,
        ?string $model = null,
        ?int $timeout = null
    ): AgentResponse {
        GeminiRateLimiter::ensurePacing();

        return $this->prompt($prompt, $attachments, $provider, $model, $timeout);
    }
}
