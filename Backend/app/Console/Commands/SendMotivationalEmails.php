<?php

namespace App\Console\Commands;

use App\Ai\Agents\MotivationalMailAgent;
use App\Mail\MotivationalMail;
use App\Models\DailyMission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMotivationalEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-motivational {--user= : Optional specific user ID for testing} {--force : Force send ignoring daily check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AI-generated motivational emails to users who haven\'t completed daily missions for 3+ consecutive days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting motivational email dispatch for inactive users...');

        $query = User::query()
            ->where('is_onboarded', true)
            ->where('mission_email_enabled', true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $query->chunkById(50, function ($users) use (&$sentCount, &$skippedCount, &$failedCount) {
            foreach ($users as $user) {
                try {
                    // Calculate consecutive days of inactivity
                    $inactiveDays = $this->getConsecutiveInactiveDays($user);

                    // Only send if inactive for 3+ days
                    if ($inactiveDays < 3) {
                        $skippedCount++;
                        continue;
                    }

                    // Don't send if already sent today (unless --force)
                    if (!$this->option('force') && $user->last_motivational_mail_sent_at &&
                        $user->last_motivational_mail_sent_at->isToday()) {
                        $skippedCount++;
                        continue;
                    }

                    // Generate AI motivational content
                    $agent = new MotivationalMailAgent($user, $inactiveDays);
                    $result = $agent->prompt('Write a personalized motivational email for this user');
                    $aiContent = is_array($result) ? $result : json_decode((string) $result, true);

                    if (empty($aiContent) || empty($aiContent['body'])) {
                        // Fallback content if AI generation fails
                        $aiContent = $this->getFallbackContent($user, $inactiveDays);
                    }

                    // Queue the email
                    Mail::to($user->email)->queue(new MotivationalMail($user, $aiContent, $inactiveDays));

                    // Update tracking timestamp
                    $user->update(['last_motivational_mail_sent_at' => now()]);

                    $sentCount++;
                    $this->line("Queued motivational email for: {$user->email} (inactive {$inactiveDays} days)");

                } catch (\Throwable $e) {
                    $failedCount++;
                    Log::error("Failed to send motivational email for user {$user->id}: {$e->getMessage()}", [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Error processing user {$user->email}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Motivational email dispatch completed. Sent: {$sentCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }

    /**
     * Calculate how many consecutive days a user has NOT completed any daily mission.
     */
    private function getConsecutiveInactiveDays(User $user): int
    {
        // Check from last_mission_completed_at first (fast path)
        if ($user->last_mission_completed_at) {
            return (int) $user->last_mission_completed_at->diffInDays(today());
        }

        // Fallback: check mission history directly
        $lastCompleted = DailyMission::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('mission_date')
            ->first();

        if ($lastCompleted) {
            $lastDate = $lastCompleted->mission_date instanceof \Carbon\CarbonInterface
                ? $lastCompleted->mission_date
                : \Carbon\Carbon::parse($lastCompleted->mission_date);

            // Backfill the tracking column for future performance
            $user->update(['last_mission_completed_at' => $lastDate]);

            return (int) $lastDate->diffInDays(today());
        }

        // If user has never completed a mission, check how old their account is
        $daysSinceJoin = (int) $user->created_at->diffInDays(today());

        // Only consider them inactive if they've been around for at least 3 days
        // (give new users time to get started)
        return $daysSinceJoin >= 3 ? $daysSinceJoin : 0;
    }

    /**
     * Fallback content if AI generation fails.
     */
    private function getFallbackContent(User $user, int $inactiveDays): array
    {
        $name = $user->name ?? 'there';

        return [
            'subject' => "Hey {$name}, your future self misses you",
            'greeting' => "Hey {$name},",
            'body' => "It's been {$inactiveDays} days since you last worked on your missions, and that's okay. Life gets busy, energy dips, and sometimes we just need a pause.\n\nBut I want you to know something — the fact that you signed up for FutureSelf tells me you care about your growth. That intention hasn't gone anywhere. It's still right there, waiting for you.\n\nYou don't need to make up for lost time. You just need one small action today.",
            'actionable_step' => "Open FutureSelf and just look at your current goal. Don't pressure yourself to complete anything — just reconnect with why you started. Sometimes that's all it takes to get the momentum flowing again.",
            'closing' => 'Your Future Self',
        ];
    }
}
