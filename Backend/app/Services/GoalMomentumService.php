<?php

namespace App\Services;

use App\Models\DailyMission;
use App\Models\Goals;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoalMomentumService
{
    /**
     * Component weights for momentum score calculation.
     * Modular and configurable.
     *
     * @var array<string, float>
     */
    protected array $weights = [
        'mission_completion' => 0.40,
        'consistency' => 0.30,
        'recent_activity' => 0.20,
        'check_in_reflection' => 0.10,
    ];

    public function __construct(array $customWeights = [])
    {
        if (!empty($customWeights)) {
            $this->setWeights($customWeights);
        }
    }

    /**
     * Set or override component weights.
     */
    public function setWeights(array $weights): self
    {
        $this->weights = array_merge($this->weights, $weights);
        return $this;
    }

    /**
     * Get current weights.
     */
    public function getWeights(): array
    {
        return $this->weights;
    }

    /**
     * Calculate comprehensive Goal Momentum and tracking metrics for a given Goal.
     */
    public function calculate(Goals $goal): array
    {
        $user = $goal->user ?? User::find($goal->user_id);
        
        // Fetch all missions linked to this goal (ordered by mission_date)
        $missions = DailyMission::where('goal_id', $goal->id)
            ->orderBy('mission_date')
            ->get();

        // If goal has no directly linked missions, check if user has missions with null goal_id (fallback for legacy single-goal records)
        if ($missions->isEmpty() && $user) {
            $userTotalGoalsCount = Goals::where('user_id', $user->id)->count();
            if ($userTotalGoalsCount === 1) {
                $missions = DailyMission::where('user_id', $user->id)
                    ->orderBy('mission_date')
                    ->get();
            }
        }

        $totalAssigned = $missions->count();
        $completedMissions = $missions->where('status', 'completed');
        $skippedMissions = $missions->where('status', 'skipped');
        $pendingMissions = $missions->where('status', 'pending');

        $completedCount = $completedMissions->count();
        $skippedCount = $skippedMissions->count();
        $pendingCount = $pendingMissions->count();

        // 1. Mission Completion Rate & Sub-Score (Weight: 40%)
        $completionRate = $totalAssigned > 0 
            ? round(($completedCount / $totalAssigned) * 100, 1) 
            : 0.0;
        $completionSubScore = $completionRate;

        // 2. Consistency Sub-Score (Weight: 30%)
        $streak = $this->calculateGoalStreak($completedMissions, $user);
        $consistencySubScore = $this->calculateConsistencyScore($completedMissions, $streak);

        // 3. Recent Activity Sub-Score (Weight: 20%)
        $recentActivitySubScore = $this->calculateRecentActivityScore($completedMissions);

        // 4. Check-in & Reflection Sub-Score (Weight: 10%)
        $reflectionSubScore = $this->calculateReflectionScore($completedMissions);

        // Calculate Weighted Momentum Score (0 - 100)
        $rawScore = (
            ($completionSubScore * ($this->weights['mission_completion'] ?? 0.40)) +
            ($consistencySubScore * ($this->weights['consistency'] ?? 0.30)) +
            ($recentActivitySubScore * ($this->weights['recent_activity'] ?? 0.20)) +
            ($reflectionSubScore * ($this->weights['check_in_reflection'] ?? 0.10))
        );

        $momentumScore = max(0, min(100, (int) round($rawScore)));

        // Trend calculation (Current 7 days vs Previous 7 days)
        $trendData = $this->calculateTrend($completedMissions);

        // Determine Momentum Status
        $status = $this->determineStatus($goal, $momentumScore);

        // Total Focus Minutes Invested
        $focusMinutes = (int) $completedMissions->sum('estimated_minutes');

        // Total Unique Active Days
        $activeDays = $completedMissions
            ->pluck('mission_date')
            ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : Carbon::parse($d)->toDateString())
            ->unique()
            ->count();

        // Weekly Day-by-Day Activity Breakdown (Last 7 Days)
        $weeklyMetrics = $this->buildWeeklyMetrics($missions);

        return [
            'score' => $momentumScore,
            'status' => $status,
            'mission_completion_rate' => $completionRate,
            'missions_completed' => $completedCount,
            'missions_assigned' => $totalAssigned,
            'missions_skipped' => $skippedCount,
            'missions_pending' => $pendingCount,
            'current_streak' => $streak,
            'active_days' => $activeDays,
            'focus_minutes' => $focusMinutes,
            'recent_trend' => $trendData['trend'],
            'trend_details' => $trendData,
            'sub_scores' => [
                'mission_completion' => round($completionSubScore, 1),
                'consistency' => round($consistencySubScore, 1),
                'recent_activity' => round($recentActivitySubScore, 1),
                'check_in_reflection' => round($reflectionSubScore, 1),
            ],
            'weights' => $this->weights,
            'weekly_metrics' => $weeklyMetrics,
            'target' => $goal->getTargetProgress(),
        ];
    }

    /**
     * Calculate goal streak (consecutive days of completed missions).
     */
    protected function calculateGoalStreak(Collection $completedMissions, ?User $user): int
    {
        if ($completedMissions->isEmpty()) {
            return 0;
        }

        $completedDates = $completedMissions
            ->pluck('mission_date')
            ->map(fn ($date) => $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        $todayStr = today()->toDateString();
        $yesterdayStr = today()->subDay()->toDateString();

        $checkDate = today();

        if ($completedDates->contains($todayStr)) {
            $checkDate = today();
        } elseif ($completedDates->contains($yesterdayStr)) {
            $checkDate = today()->subDay();
        } else {
            return 0;
        }

        $streak = 0;
        while ($completedDates->contains($checkDate->toDateString())) {
            $streak++;
            $checkDate = $checkDate->subDay();
        }

        return $streak;
    }

    /**
     * Consistency score based on active streak and 14-day completion frequency.
     */
    protected function calculateConsistencyScore(Collection $completedMissions, int $streak): float
    {
        if ($completedMissions->isEmpty()) {
            return 0.0;
        }

        // Streak factor: 7+ day streak = 100% of streak portion
        $streakFactor = min(100.0, ($streak / 7) * 100);

        // 14-Day activity factor: Active on 8+ of the last 14 days = 100%
        $fourteenDaysAgo = today()->subDays(13)->toDateString();
        $completedLast14DaysCount = $completedMissions
            ->filter(function ($m) use ($fourteenDaysAgo) {
                $dateStr = $m->mission_date instanceof Carbon ? $m->mission_date->toDateString() : Carbon::parse($m->mission_date)->toDateString();
                return $dateStr >= $fourteenDaysAgo;
            })
            ->pluck('mission_date')
            ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : Carbon::parse($d)->toDateString())
            ->unique()
            ->count();

        $frequencyFactor = min(100.0, ($completedLast14DaysCount / 8) * 100);

        return round(($streakFactor * 0.5) + ($frequencyFactor * 0.5), 1);
    }

    /**
     * Recent activity score (last 7 days volume + recency of last completion).
     */
    protected function calculateRecentActivityScore(Collection $completedMissions): float
    {
        if ($completedMissions->isEmpty()) {
            return 0.0;
        }

        $sevenDaysAgo = today()->subDays(6)->toDateString();

        // Count completions in the last 7 days (4+ completions = 100% volume)
        $completedLast7Days = $completedMissions
            ->filter(function ($m) use ($sevenDaysAgo) {
                $dateStr = $m->mission_date instanceof Carbon ? $m->mission_date->toDateString() : Carbon::parse($m->mission_date)->toDateString();
                return $dateStr >= $sevenDaysAgo;
            });

        $volumeScore = min(100.0, ($completedLast7Days->count() / 4) * 100);

        // Recency factor based on most recent completed mission
        $latestCompleted = $completedMissions->sortByDesc('mission_date')->first();
        $recencyScore = 0.0;

        if ($latestCompleted) {
            $latestDate = $latestCompleted->mission_date instanceof Carbon 
                ? $latestCompleted->mission_date->startOfDay() 
                : Carbon::parse($latestCompleted->mission_date)->startOfDay();

            $daysAgo = (int) $latestDate->diffInDays(today()->startOfDay(), false);

            if ($daysAgo <= 0) {
                $recencyScore = 100.0;
            } elseif ($daysAgo === 1) {
                $recencyScore = 85.0;
            } elseif ($daysAgo === 2) {
                $recencyScore = 70.0;
            } elseif ($daysAgo === 3) {
                $recencyScore = 50.0;
            } elseif ($daysAgo <= 6) {
                $recencyScore = 30.0;
            } else {
                $recencyScore = 0.0;
            }
        }

        return round(($volumeScore * 0.6) + ($recencyScore * 0.4), 1);
    }

    /**
     * Reflection and check-in score based on thoughtful completion notes.
     */
    protected function calculateReflectionScore(Collection $completedMissions): float
    {
        if ($completedMissions->isEmpty()) {
            return 0.0;
        }

        $withReflectionCount = $completedMissions
            ->filter(fn ($m) => !empty(trim((string) $m->reflection)))
            ->count();

        $ratio = $withReflectionCount / $completedMissions->count();
        return round($ratio * 100, 1);
    }

    /**
     * Calculate 7-day comparative activity trend (improving, stable, declining).
     */
    protected function calculateTrend(Collection $completedMissions): array
    {
        $today = today();
        $currentPeriodStart = $today->copy()->subDays(6)->toDateString();
        $previousPeriodStart = $today->copy()->subDays(13)->toDateString();
        $previousPeriodEnd = $today->copy()->subDays(7)->toDateString();

        $currentPeriodCompletions = $completedMissions->filter(function ($m) use ($currentPeriodStart) {
            $dateStr = $m->mission_date instanceof Carbon ? $m->mission_date->toDateString() : Carbon::parse($m->mission_date)->toDateString();
            return $dateStr >= $currentPeriodStart;
        })->count();

        $previousPeriodCompletions = $completedMissions->filter(function ($m) use ($previousPeriodStart, $previousPeriodEnd) {
            $dateStr = $m->mission_date instanceof Carbon ? $m->mission_date->toDateString() : Carbon::parse($m->mission_date)->toDateString();
            return $dateStr >= $previousPeriodStart && $dateStr <= $previousPeriodEnd;
        })->count();

        $diff = $currentPeriodCompletions - $previousPeriodCompletions;

        if ($diff >= 1) {
            $trend = 'improving';
        } elseif ($diff <= -1) {
            $trend = 'declining';
        } else {
            $trend = 'stable';
        }

        return [
            'trend' => $trend,
            'current_period_completions' => $currentPeriodCompletions,
            'previous_period_completions' => $previousPeriodCompletions,
            'diff' => $diff,
        ];
    }

    /**
     * Map momentum score and goal status to categorical status.
     * Status values: 'on_track', 'needs_attention', 'at_risk', 'achieved'.
     */
    protected function determineStatus(Goals $goal, int $momentumScore): string
    {
        // 1. If goal is explicitly marked as completed or target achieved
        if ($goal->status === 'completed') {
            return 'achieved';
        }

        if ($goal->isMeasurable()) {
            $target = (float) $goal->target_value;
            $current = (float) ($goal->current_value ?? 0);
            if ($target > 0 && $current >= $target) {
                return 'achieved';
            }
        }

        // 2. Momentum score based status
        if ($momentumScore >= 70) {
            return 'on_track';
        }

        if ($momentumScore >= 40) {
            return 'needs_attention';
        }

        return 'at_risk';
    }

    /**
     * Build day-by-day metrics for the last 7 days.
     */
    protected function buildWeeklyMetrics(Collection $missions): array
    {
        $metrics = [];
        $missionsByDate = $missions->keyBy(function ($m) {
            return $m->mission_date instanceof Carbon 
                ? $m->mission_date->toDateString() 
                : Carbon::parse($m->mission_date)->toDateString();
        });

        for ($i = 6; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $dateStr = $day->toDateString();
            $mission = $missionsByDate->get($dateStr);

            $metrics[] = [
                'date' => $dateStr,
                'day_name' => $day->format('D'),
                'day_short' => $day->format('M j'),
                'has_mission' => (bool) $mission,
                'status' => $mission ? $mission->status : 'none',
                'focus_minutes' => ($mission && $mission->status === 'completed') ? (int) $mission->estimated_minutes : 0,
                'title' => $mission?->title,
                'mission_id' => $mission?->id,
            ];
        }

        return $metrics;
    }
}
