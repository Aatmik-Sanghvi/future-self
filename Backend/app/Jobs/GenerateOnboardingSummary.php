<?php

namespace App\Jobs;

use App\Ai\Agents\OnboardingSummary;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateOnboardingSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 3;

    /**
     * Define middleware to space out jobs and prevent hitting 15 RPM.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('gemini-jobs')];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("🚀 [GenerateOnboardingSummary] Starting job for User #{$this->userId} (Attempt {$this->attempts()}/{$this->tries})");

        // Authenticate as the user so the agent can access their data
        auth()->loginUsingId($this->userId);

        $response = (new OnboardingSummary)->prompt('Summarize it in brief in 100 words...');
        
        User::where('id', $this->userId)->update([
            'future_self_summary' => $response['feedback'],
        ]);

        Log::info("✅ [GenerateOnboardingSummary] Successfully generated and saved summary for User #{$this->userId}");
    }
}
