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
        $currentHour = now()->format('H:00');
        $currentShortHour = now()->format('H');
        $this->info("Checking pending mission reminders for time slot: {$currentHour}...");

        $query = User::query()
            ->where('is_onboarded', true)
            ->where('mission_reminder_enabled', true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        } elseif (!$this->option('force')) {
            // Match reminder times like "19:00", "19:00:00", "19:30" or "7 PM"
            $query->where(function ($q) use ($currentHour, $currentShortHour) {
                $q->where('mission_reminder_time', 'like', "{$currentShortHour}:%")
                  ->orWhere('mission_reminder_time', $currentHour)
                  ->orWhereNull('mission_reminder_time'); // defaults to 19:00 if 19:00 slot
            });
        }

        $users = $query->get();
        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

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

                Mail::to($user->email)->send(new DailyMissionReminderMail($user, $mission));
                $mission->update(['reminder_mail_sent_at' => now()]);
                $sentCount++;
                $this->line("Sent mission reminder to: {$user->email} for mission '{$mission->title}'");
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Failed to send mission reminder to user {$user->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error sending reminder to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Reminder check completed. Sent: {$sentCount}, Skipped (Completed/Sent): {$skippedCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }
}
