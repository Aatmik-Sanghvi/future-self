<?php

namespace App\Console\Commands;

use App\Mail\DailyMissionReminderMail;
use App\Models\DailyMission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyMissionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missions:send-reminders {--user= : Optional specific user ID for testing} {--force : Force send reminder ignoring time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send evening mission reminder emails to users with pending missions at their configured reminder time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentTime = now()->format('H:i');
        $this->info("Checking pending mission reminders for time slot: {$currentTime}...");

        $query = User::query()
            ->where('is_onboarded', true)
            ->where('mission_reminder_enabled', true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        } elseif (!$this->option('force')) {
            // Match reminder times for the current 30-min slot (e.g. "16:30" or "16:30:00")
            $query->where(function ($q) use ($currentTime) {
                $q->where('mission_reminder_time', $currentTime)
                  ->orWhere('mission_reminder_time', $currentTime . ':00');

                // Default reminder time is 19:00 (7:00 PM) for users without a custom time set
                if ($currentTime === '19:00') {
                    $q->orWhereNull('mission_reminder_time');
                }
            });
        }

        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $query->chunkById(100, function ($users) use (&$sentCount, &$skippedCount, &$failedCount) {
            foreach ($users as $user) {
                try {
                    // Find today's pending mission
                    $mission = DailyMission::today()
                        ->where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->first();

                    // If already completed or not found, skip
                    if (!$mission) {
                        $skippedCount++;
                        continue;
                    }

                    // If reminder already sent today, skip unless force flag is passed
                    if (!empty($mission->reminder_mail_sent_at) && !$this->option('force')) {
                        $skippedCount++;
                        continue;
                    }

                    Mail::to($user->email)->queue(new DailyMissionReminderMail($user, $mission));
                    $mission->update(['reminder_mail_sent_at' => now()]);
                    $sentCount++;
                    $this->line("Queued mission reminder for: {$user->email} (mission: '{$mission->title}')");
                } catch (\Throwable $e) {
                    $failedCount++;
                    Log::error("Failed to process mission reminder for user {$user->id}: {$e->getMessage()}", [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->error("Error processing reminder for {$user->email}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Reminder check completed. Queued/Dispatched: {$sentCount}, Skipped (Completed/Sent): {$skippedCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }
}
