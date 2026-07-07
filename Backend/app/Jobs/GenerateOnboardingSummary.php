<?php

namespace App\Jobs;

use App\Ai\Agents\OnboardingSummary;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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
    public int $tries = 3;

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
        // Authenticate as the user so the agent can access their data
        auth()->loginUsingId($this->userId);

        $response = (new OnboardingSummary)->prompt('Summarize it in brief in 100 words...');
        
        User::where('id', $this->userId)->update([
            'future_self_summary' => $response['feedback'],
        ]);

        Log::info("Onboarding summary generated for user {$this->userId}");
    }
}
