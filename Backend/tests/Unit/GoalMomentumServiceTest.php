<?php

namespace Tests\Unit;

use App\Models\DailyMission;
use App\Models\Goals;
use App\Models\User;
use App\Services\GoalMomentumService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalMomentumServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GoalMomentumService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GoalMomentumService();
    }

    /**
     * 1. Test new goal with no missions.
     */
    public function test_new_goal_with_no_missions_has_zero_score_and_at_risk_status(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Learn Quantum Computing',
            'status' => 'active',
        ]);

        $result = $this->service->calculate($goal);

        $this->assertEquals(0, $result['score']);
        $this->assertEquals('at_risk', $result['status']);
        $this->assertEquals(0.0, $result['mission_completion_rate']);
        $this->assertEquals(0, $result['missions_completed']);
        $this->assertEquals(0, $result['missions_assigned']);
        $this->assertEquals(0, $result['current_streak']);
        $this->assertEquals(0, $result['focus_minutes']);
        $this->assertEquals('stable', $result['recent_trend']);
        $this->assertNull($result['target']);
    }

    /**
     * 2. Test goal with one completed mission today.
     */
    public function test_one_completed_mission(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Daily Meditation Practice',
            'status' => 'active',
        ]);

        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->toDateString(),
            'title' => '10 min mindfulness session',
            'description' => 'Breathe and observe thoughts.',
            'status' => 'completed',
            'completed_at' => now(),
            'estimated_minutes' => 15,
            'reflection' => 'Felt very calm after this session.',
        ]);

        $result = $this->service->calculate($goal);

        $this->assertEquals(100.0, $result['mission_completion_rate']);
        $this->assertEquals(1, $result['missions_completed']);
        $this->assertEquals(1, $result['missions_assigned']);
        $this->assertEquals(1, $result['current_streak']);
        $this->assertEquals(15, $result['focus_minutes']);
        $this->assertGreaterThan(0, $result['score']);
        $this->assertEquals('improving', $result['recent_trend']);
    }

    /**
     * 3. Test multiple completed missions.
     */
    public function test_multiple_completed_missions(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Write a novel',
            'status' => 'active',
        ]);

        for ($i = 4; $i >= 0; $i--) {
            DailyMission::create([
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'mission_date' => today()->subDays($i)->toDateString(),
                'title' => "Write chapter {$i}",
                'description' => 'Draft 500 words',
                'status' => 'completed',
                'completed_at' => now()->subDays($i),
                'estimated_minutes' => 30,
                'reflection' => "Made solid progress on chapter {$i}.",
            ]);
        }

        $result = $this->service->calculate($goal);

        $this->assertEquals(100.0, $result['mission_completion_rate']);
        $this->assertEquals(5, $result['missions_completed']);
        $this->assertEquals(5, $result['missions_assigned']);
        $this->assertEquals(5, $result['current_streak']);
        $this->assertEquals(150, $result['focus_minutes']);
        $this->assertGreaterThanOrEqual(70, $result['score']);
        $this->assertEquals('on_track', $result['status']);
    }

    /**
     * 4. Test skipped missions affect completion rate and momentum score.
     */
    public function test_skipped_missions_result_in_lower_score(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Gym training',
            'status' => 'active',
        ]);

        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->subDays(2)->toDateString(),
            'title' => 'Leg day',
            'description' => 'Squats and lunges',
            'status' => 'skipped',
            'estimated_minutes' => 45,
        ]);

        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->subDays(1)->toDateString(),
            'title' => 'Cardio',
            'description' => '20 min jog',
            'status' => 'skipped',
            'estimated_minutes' => 20,
        ]);

        $result = $this->service->calculate($goal);

        $this->assertEquals(0.0, $result['mission_completion_rate']);
        $this->assertEquals(0, $result['missions_completed']);
        $this->assertEquals(2, $result['missions_assigned']);
        $this->assertEquals(2, $result['missions_skipped']);
        $this->assertEquals(0, $result['current_streak']);
        $this->assertEquals(0, $result['score']);
        $this->assertEquals('at_risk', $result['status']);
    }

    /**
     * 5. Test strong consistency produces high momentum score.
     */
    public function test_strong_consistency_produces_high_momentum(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Consistent Coding Habit',
            'status' => 'active',
        ]);

        for ($i = 9; $i >= 0; $i--) {
            DailyMission::create([
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'mission_date' => today()->subDays($i)->toDateString(),
                'title' => "Day {$i} Coding",
                'description' => 'Solve 1 algorithmic puzzle',
                'status' => 'completed',
                'completed_at' => now()->subDays($i),
                'estimated_minutes' => 25,
                'reflection' => 'Solved efficiently.',
            ]);
        }

        $result = $this->service->calculate($goal);

        $this->assertGreaterThanOrEqual(85, $result['score']);
        $this->assertEquals('on_track', $result['status']);
        $this->assertEquals(10, $result['current_streak']);
        $this->assertEquals(10, $result['active_days']);
    }

    /**
     * 6. Test declining activity trend when previous 7 days had more completions than current 7 days.
     */
    public function test_declining_activity_trend(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Language Learning',
            'status' => 'active',
        ]);

        // 4 missions completed in previous period (days 8 to 11 ago)
        for ($i = 11; $i >= 8; $i--) {
            DailyMission::create([
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'mission_date' => today()->subDays($i)->toDateString(),
                'title' => "German vocabulary {$i}",
                'description' => 'Practice 20 flashcards',
                'status' => 'completed',
                'completed_at' => now()->subDays($i),
                'estimated_minutes' => 15,
            ]);
        }

        // Only 1 mission completed in current period (today)
        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->toDateString(),
            'title' => 'German vocabulary today',
            'description' => 'Practice 5 flashcards',
            'status' => 'completed',
            'completed_at' => now(),
            'estimated_minutes' => 15,
        ]);

        $result = $this->service->calculate($goal);

        $this->assertEquals('declining', $result['recent_trend']);
        $this->assertEquals(1, $result['trend_details']['current_period_completions']);
        $this->assertEquals(4, $result['trend_details']['previous_period_completions']);
    }

    /**
     * 7. Test improving activity trend when current period has more completions than previous period.
     */
    public function test_improving_activity_trend(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Guitar Mastery',
            'status' => 'active',
        ]);

        // 1 mission in previous period (day 10 ago)
        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->subDays(10)->toDateString(),
            'title' => 'Scale practice',
            'description' => 'Minor pentatonic',
            'status' => 'completed',
            'completed_at' => now()->subDays(10),
            'estimated_minutes' => 20,
        ]);

        // 3 missions in current period (days 2, 1, 0 ago)
        for ($i = 2; $i >= 0; $i--) {
            DailyMission::create([
                'user_id' => $user->id,
                'goal_id' => $goal->id,
                'mission_date' => today()->subDays($i)->toDateString(),
                'title' => "Chord transitions {$i}",
                'description' => 'F to G transitions',
                'status' => 'completed',
                'completed_at' => now()->subDays($i),
                'estimated_minutes' => 20,
            ]);
        }

        $result = $this->service->calculate($goal);

        $this->assertEquals('improving', $result['recent_trend']);
        $this->assertEquals(3, $result['trend_details']['current_period_completions']);
        $this->assertEquals(1, $result['trend_details']['previous_period_completions']);
    }

    /**
     * 8. Test no activity on dormant goal.
     */
    public function test_no_activity_on_dormant_goal(): void
    {
        $user = User::factory()->create();
        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Old Goal',
            'status' => 'active',
        ]);

        // Old mission from 30 days ago
        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->subDays(30)->toDateString(),
            'title' => 'Old action',
            'description' => 'Action done long ago',
            'status' => 'completed',
            'completed_at' => now()->subDays(30),
            'estimated_minutes' => 15,
        ]);

        $result = $this->service->calculate($goal);

        // Streak has expired
        $this->assertEquals(0, $result['current_streak']);
        $this->assertEquals('stable', $result['recent_trend']);
        $this->assertLessThan(60, $result['score']);
    }

    /**
     * 10. Test measurable vs non-measurable goals.
     */
    public function test_measurable_goal_calculates_target_separately(): void
    {
        $user = User::factory()->create();

        // Measurable goal
        $measurableGoal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Run 100 km',
            'status' => 'active',
            'target_value' => 100.0,
            'current_value' => 42.0,
            'unit' => 'km',
        ]);

        $result = $this->service->calculate($measurableGoal);

        $this->assertNotNull($result['target']);
        $this->assertTrue($result['target']['is_measurable']);
        $this->assertEquals(100.0, $result['target']['target_value']);
        $this->assertEquals(42.0, $result['target']['current_value']);
        $this->assertEquals('km', $result['target']['unit']);
        $this->assertEquals(42.0, $result['target']['percentage']);
        $this->assertFalse($result['target']['is_target_reached']);

        // Non-measurable goal
        $nonMeasurableGoal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Become a better listener',
            'status' => 'active',
        ]);

        $nonMeasurableResult = $this->service->calculate($nonMeasurableGoal);
        $this->assertNull($nonMeasurableResult['target']);
    }
}
