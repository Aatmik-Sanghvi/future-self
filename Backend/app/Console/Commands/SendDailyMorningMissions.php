<?php

namespace App\Console\Commands;

use App\Ai\Agents\DailyMissionAgent;
use App\Mail\DailyMissionMail;
use App\Models\DailyMission;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyMorningMissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missions:send-morning {--user= : Optional specific user ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily morning mission emails at 8:00 AM to active users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting 8:00 AM Daily Morning Mission email dispatch...');

        $query = User::query()
            ->where('is_onboarded', true)
            ->where('mission_email_enabled', true);

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                // 1. Get or generate today's mission
                $mission = DailyMission::today()->where('user_id', $user->id)->first();

                if (!$mission) {
                    // Check if today's mood was already recorded
                    $todayMood = Mood::where('user_id', $user->id)
                        ->whereDate('mood_date', today())
                        ->first();

                    $agent = new DailyMissionAgent($user, $todayMood?->mood_type);
                    $result = $agent->ask('Generate today daily mission');
                    $taskData = is_array($result) ? $result : json_decode((string) $result, true);

                    $activeGoal = $user->goals()->where('status', 'active')->first();

                    $mission = DailyMission::create([
                        'user_id' => $user->id,
                        'goal_id' => $activeGoal?->id,
                        'mission_date' => today(),
                        'title' => $taskData['title'] ?? 'Daily Progress Action',
                        'description' => $taskData['description'] ?? 'Take one meaningful step towards your future self.',
                        'future_self_note' => $taskData['future_self_note'] ?? 'Every small action builds the foundation for tomorrow.',
                        'category' => $taskData['category'] ?? 'Mindset',
                        'mood_type' => $todayMood?->mood_type ?? 'neutral',
                        'estimated_minutes' => $taskData['estimated_minutes'] ?? 15,
                        'difficulty' => $taskData['difficulty'] ?? 'medium',
                        'status' => 'pending',
                    ]);
                }

                // 2. Send email if not already sent today
                if (empty($mission->morning_mail_sent_at)) {
                    Mail::to($user->email)->send(new DailyMissionMail($user, $mission));
                    $mission->update(['morning_mail_sent_at' => now()]);
                    $sentCount++;
                    $this->line("Sent morning mission to: {$user->email}");
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Failed to send morning mission to user {$user->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error sending to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Completed. Sent: {$sentCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }
}
