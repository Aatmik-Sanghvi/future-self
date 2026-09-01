<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\DailyMissionAgent;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\DailyMission;
use App\Models\Goals;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyMissionController extends Controller
{
    /**
     * Get or automatically generate today's daily mission.
     */
    public function today(Request $request)
    {
        $user = auth()->user();
        $today = today()->toDateString();

        $mission = DailyMission::where('user_id', $user->id)
            ->whereDate('mission_date', $today)
            ->first();

        // If no mission exists yet and user has at least started onboarding, auto-generate
        if (! $mission && $user->is_onboarded) {
            $mission = $this->generateMissionForUser($user);
        }

        $todayMood = Mood::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        $stats = $this->getUserMissionStats($user->id);

        return ResponseHelper::send(200, 'Today\'s mission fetched successfully.', [
            'mission' => $mission,
            'today_mood' => $todayMood?->mood_type,
            'is_mood_checked_in' => (bool) $todayMood,
            'stats' => $stats,
        ]);
    }

    /**
     * Generate or regenerate today's daily mission.
     */
    public function generate(Request $request)
    {
        $user = auth()->user();
        $today = today()->toDateString();

        $existing = DailyMission::where('user_id', $user->id)
            ->whereDate('mission_date', $today)
            ->first();

        if ($existing && $existing->status === 'completed') {
            return ResponseHelper::send(400, 'You have already accomplished today\'s mission! Great job!');
        }

        $mission = $this->generateMissionForUser($user, $request->input('mood_type'), $existing);

        $stats = $this->getUserMissionStats($user->id);

        return ResponseHelper::send(200, 'Daily mission generated successfully.', [
            'mission' => $mission,
            'stats' => $stats,
        ]);
    }

    /**
     * Mark a mission as completed after honesty confirmation.
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'reflection' => 'nullable|string|max:1000',
            'confirmed_honest' => 'required|boolean',
        ]);

        if (! $request->boolean('confirmed_honest')) {
            return ResponseHelper::send(422, 'Please confirm that you genuinely completed this mission.');
        }

        $user = auth()->user();
        $mission = DailyMission::where('user_id', $user->id)->find($id);

        if (! $mission) {
            return ResponseHelper::send(404, 'Mission not found.');
        }

        if ($mission->status === 'completed') {
            return ResponseHelper::send(200, 'Mission is already marked as completed.', [
                'mission' => $mission,
                'daily_streak' => $user->daily_streak,
            ]);
        }

        $mission->update([
            'status' => 'completed',
            'completed_at' => now(),
            'reflection' => $request->input('reflection'),
        ]);

        $streak = $user->syncDailyStreak();
        $stats = $this->getUserMissionStats($user->id);

        return ResponseHelper::send(200, 'Outstanding work! Mission marked as completed.', [
            'mission' => $mission,
            'stats' => $stats,
            'daily_streak' => $streak,
        ]);
    }

    /**
     * Get paginated history of past missions with comprehensive statistics.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status'); // 'all', 'completed', 'pending'
        $category = $request->query('category');
        $perPage = min((int) ($request->query('per_page', 10)), 50);

        $query = DailyMission::where('user_id', $user->id)
            ->orderByDesc('mission_date');

        if ($status && in_array($status, ['completed', 'pending', 'skipped'])) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $missions = $query->paginate($perPage);
        $stats = $this->getUserMissionStats($user->id);

        return ResponseHelper::send(200, 'Mission history retrieved successfully.', [
            'missions' => $missions,
            'stats' => $stats,
        ]);
    }

    /**
     * Helper to generate a daily mission via DailyMissionAgent.
     */
    protected function generateMissionForUser(User $user, ?string $moodType = null, ?DailyMission $existing = null): DailyMission
    {
        // Find latest mood if not provided
        if (! $moodType) {
            $latestMood = Mood::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->latest()
                ->first();
            $moodType = $latestMood?->mood_type ?? 'neutral';
        }

        $goal = Goals::where('user_id', $user->id)->latest()->first();

        try {
            $agent = new DailyMissionAgent($user, $moodType);
            $response = $agent->prompt("Generate today's mission for {$user->name}.");
            $data = is_array($response) ? $response : (json_decode($response->text ?? '{}', true) ?: []);
        } catch (\Exception $e) {
            Log::error('DailyMissionAgent generation error: ' . $e->getMessage());
            // Fallback mission in case of AI provider downtime
            $data = [
                'title' => 'Momentum Step: 15-Minute Goal Focus',
                'description' => 'Spend 15 undistracted minutes reviewing your goal and completing one single micro-action.',
                'future_self_note' => 'Consistency is built in small steps. Give this 15 minutes of your full presence today.',
                'category' => $goal?->category ?? 'Growth',
                'estimated_minutes' => 15,
                'difficulty' => ($moodType === 'exhausted' || $moodType === 'sad') ? 'easy' : 'medium',
            ];
        }

        $missionAttributes = [
            'user_id' => $user->id,
            'goal_id' => $goal?->id,
            'mission_date' => today()->toDateString(),
            'title' => $data['title'] ?? 'Daily Goal Milestone',
            'description' => $data['description'] ?? 'Take one concrete step towards your goals today.',
            'future_self_note' => $data['future_self_note'] ?? 'Every small action today shapes who you will become tomorrow.',
            'category' => $data['category'] ?? ($goal?->category ?? 'Focus'),
            'mood_type' => $moodType,
            'estimated_minutes' => (int) ($data['estimated_minutes'] ?? 15),
            'difficulty' => in_array($data['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $data['difficulty'] : 'medium',
            'status' => 'pending',
        ];

        if ($existing) {
            $existing->update($missionAttributes);
            return $existing->fresh();
        }

        return DailyMission::updateOrCreate(
            ['user_id' => $user->id, 'mission_date' => today()->toDateString()],
            $missionAttributes
        );
    }

    /**
     * Get user's mission email and reminder preferences.
     */
    public function getReminderSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'mission_email_enabled' => (bool) ($user->mission_email_enabled ?? true),
                'mission_reminder_time' => $user->mission_reminder_time ?? '19:00',
                'mission_reminder_enabled' => (bool) ($user->mission_reminder_enabled ?? true),
                'default_reminder_time' => '19:00',
            ],
        ]);
    }

    /**
     * Update user's mission email and reminder preferences.
     */
    public function updateReminderSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'mission_email_enabled' => 'nullable|boolean',
            'mission_reminder_time' => [
                'nullable',
                'string',
                'regex:/^(1[6-9]|2[0-3]):(00|30)$/',
            ],
            'mission_reminder_enabled' => 'nullable|boolean',
        ], [
            'mission_reminder_time.regex' => 'Reminder time must be at 4:00 PM or later in 30-minute intervals (e.g. 4:00 PM, 4:30 PM).',
        ]);

        $updateData = [];

        if (array_key_exists('mission_email_enabled', $validated)) {
            $updateData['mission_email_enabled'] = (bool) $validated['mission_email_enabled'];
        }

        if (!empty($validated['mission_reminder_time'])) {
            $updateData['mission_reminder_time'] = $validated['mission_reminder_time'];
        }

        if (array_key_exists('mission_reminder_enabled', $validated)) {
            $updateData['mission_reminder_enabled'] = (bool) $validated['mission_reminder_enabled'];
        }

        $user->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Mission reminder preferences saved successfully.',
            'data' => [
                'mission_email_enabled' => (bool) ($user->mission_email_enabled ?? true),
                'mission_reminder_time' => $user->mission_reminder_time ?? '19:00',
                'mission_reminder_enabled' => (bool) ($user->mission_reminder_enabled ?? true),
            ],
        ]);
    }

    /**
     * Compute user's mission statistics.
     */
    protected function getUserMissionStats(int $userId): array
    {
        $total = DailyMission::where('user_id', $userId)->count();
        $completed = DailyMission::where('user_id', $userId)->where('status', 'completed')->count();
        $pending = DailyMission::where('user_id', $userId)->where('status', 'pending')->count();
        $totalMinutes = DailyMission::where('user_id', $userId)->where('status', 'completed')->sum('estimated_minutes');

        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;

        $user = User::find($userId);
        $streak = $user ? $user->syncDailyStreak() : 0;

        return [
            'total_missions' => $total,
            'completed_missions' => $completed,
            'pending_missions' => $pending,
            'completion_rate' => $completionRate,
            'total_minutes_invested' => (int) $totalMinutes,
            'daily_streak' => $streak,
        ];
    }
}
