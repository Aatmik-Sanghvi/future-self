<?php

namespace App\Console\Commands;

use App\Mail\FeedbackReminderMail;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFeedbackReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-feedback-reminders {--user= : Optional specific user ID for testing} {--force : Force send ignoring interval check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send feedback reminder emails every 2 days to users who haven\'t filled the feedback form';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting feedback reminder email dispatch...');

        $query = User::query()
            ->where('is_onboarded', true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $query->chunkById(50, function ($users) use (&$sentCount, &$skippedCount, &$failedCount) {
            foreach ($users as $user) {
                try {
                    // Check if user has already submitted feedback (not skipped)
                    $hasSubmittedFeedback = Feedback::where('user_id', $user->id)
                        ->where('is_skipped', false)
                        ->exists();

                    if ($hasSubmittedFeedback) {
                        $skippedCount++;
                        continue;
                    }

                    // Check 2-day interval since last feedback reminder (unless --force)
                    if (!$this->option('force') && $user->last_feedback_reminder_sent_at) {
                        $daysSinceLastReminder = $user->last_feedback_reminder_sent_at->diffInDays(today());

                        if ($daysSinceLastReminder < 2) {
                            $skippedCount++;
                            continue;
                        }
                    }

                    // Don't send feedback reminders to very new users (< 2 days old)
                    // Let them experience the product first
                    if ($user->created_at->diffInDays(today()) < 2) {
                        $skippedCount++;
                        continue;
                    }

                    // Queue the feedback reminder email
                    Mail::to($user->email)->queue(new FeedbackReminderMail($user));

                    // Update tracking timestamp
                    $user->update(['last_feedback_reminder_sent_at' => now()]);

                    $sentCount++;
                    $this->line("Queued feedback reminder for: {$user->email}");

                } catch (\Throwable $e) {
                    $failedCount++;
                    Log::error("Failed to send feedback reminder for user {$user->id}: {$e->getMessage()}", [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Error processing user {$user->email}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Feedback reminder dispatch completed. Sent: {$sentCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }
}
