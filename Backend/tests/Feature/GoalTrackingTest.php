<?php

namespace Tests\Feature;

use App\Models\DailyMission;
use App\Models\Goals;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GoalTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated access is rejected.
     */
    public function test_unauthenticated_user_cannot_access_goal_tracking(): void
    {
        $response = $this->getJson('/api/V1/goals/tracking');
        $response->assertStatus(401);
    }

    /**
     * Test user can get tracking data for their active goal.
     */
    public function test_authenticated_user_can_get_goal_tracking_data(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Sanctum::actingAs($user);

        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Master Machine Learning',
            'category' => 'Skill',
            'timeframe' => 'long-term',
            'priority' => 5,
            'status' => 'active',
        ]);

        DailyMission::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'mission_date' => today()->toDateString(),
            'title' => 'Implement Linear Regression from scratch',
            'description' => 'Write Python code without scikit-learn.',
            'status' => 'completed',
            'completed_at' => now(),
            'estimated_minutes' => 30,
            'reflection' => 'Understood gradient descent deeply.',
        ]);

        $response = $this->getJson('/api/V1/goals/tracking');

        $response->assertStatus(200);
        $response->assertJsonPath('data.has_goal', true);
        $response->assertJsonPath('data.goal.title', 'Master Machine Learning');
        $response->assertJsonPath('data.momentum.missions_completed', 1);
        $response->assertJsonPath('data.momentum.mission_completion_rate', 100);
        $response->assertJsonPath('data.momentum.focus_minutes', 30);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'has_goal',
                'goal',
                'momentum' => [
                    'score',
                    'status',
                    'mission_completion_rate',
                    'missions_completed',
                    'missions_assigned',
                    'current_streak',
                    'active_days',
                    'focus_minutes',
                    'recent_trend',
                    'sub_scores',
                    'weekly_metrics',
                ],
                'recent_missions',
                'all_goals',
            ],
        ]);
    }

    /**
     * 9. Test User Authorization: User A cannot retrieve or modify User B's goal.
     */
    public function test_user_cannot_access_or_modify_another_users_goal(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $goalB = Goals::create([
            'user_id' => $userB->id,
            'title' => 'User B Secret Goal',
            'status' => 'active',
        ]);

        Sanctum::actingAs($userA);

        // User A trying to view User B's goal
        $responseView = $this->getJson("/api/V1/goals/{$goalB->id}");
        $responseView->assertStatus(404);

        // User A trying to track with User B's goal_id param
        $responseTrack = $this->getJson("/api/V1/goals/tracking?goal_id={$goalB->id}");
        $responseTrack->assertStatus(404);

        // User A trying to update User B's goal
        $responseUpdate = $this->putJson("/api/V1/goals/{$goalB->id}", [
            'title' => 'Hacked title',
        ]);
        $responseUpdate->assertStatus(404);
        $this->assertEquals('User B Secret Goal', $goalB->fresh()->title);

        // User A trying to delete User B's goal
        $responseDelete = $this->deleteJson("/api/V1/goals/{$goalB->id}");
        $responseDelete->assertStatus(404);
        $this->assertDatabaseHas('goals', ['id' => $goalB->id]);

        // User A trying to update progress on User B's goal
        $responseProgress = $this->postJson("/api/V1/goals/{$goalB->id}/progress", [
            'current_value' => 50,
        ]);
        $responseProgress->assertStatus(404);
    }

    /**
     * Test goal creation via API.
     */
    public function test_user_can_create_new_goal(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/V1/goals', [
            'title' => 'Read 20 Books in 2026',
            'description' => 'Expand knowledge in psychology and business.',
            'category' => 'Wisdom',
            'timeframe' => 'long-term',
            'priority' => 4,
            'target_value' => 20,
            'current_value' => 3,
            'unit' => 'books',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('goals', [
            'user_id' => $user->id,
            'title' => 'Read 20 Books in 2026',
            'unit' => 'books',
        ]);

        $response->assertJsonPath('data.goal.target_value', 20);
        $response->assertJsonPath('data.momentum.target.percentage', 15);
    }

    /**
     * Test updating measurable progress on a goal.
     */
    public function test_user_can_update_measurable_goal_progress(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $goal = Goals::create([
            'user_id' => $user->id,
            'title' => 'Run 100 km',
            'status' => 'active',
            'target_value' => 100,
            'current_value' => 20,
            'unit' => 'km',
        ]);

        // Increment progress by 15 km
        $response = $this->postJson("/api/V1/goals/{$goal->id}/progress", [
            'increment' => 15,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(35.0, $goal->fresh()->current_value);
        $response->assertJsonPath('data.target.current_value', 35);
        $response->assertJsonPath('data.target.percentage', 35);
        $response->assertJsonPath('data.target.is_target_reached', false);

        // Reach target (e.g. set current_value to 100)
        $responseTargetReached = $this->postJson("/api/V1/goals/{$goal->id}/progress", [
            'current_value' => 100,
        ]);

        $responseTargetReached->assertStatus(200);
        $this->assertEquals(100.0, $goal->fresh()->current_value);
        $this->assertEquals('completed', $goal->fresh()->status);
        $responseTargetReached->assertJsonPath('data.momentum.status', 'achieved');
    }

    /**
     * Test list all user goals.
     */
    public function test_user_can_list_all_their_goals(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Goals::create(['user_id' => $user->id, 'title' => 'Goal 1', 'status' => 'active']);
        Goals::create(['user_id' => $user->id, 'title' => 'Goal 2', 'status' => 'completed']);

        $response = $this->getJson('/api/V1/goals');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test get a specific goal details.
     */
    public function test_user_can_get_specific_goal_details(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $goal = Goals::create(['user_id' => $user->id, 'title' => 'Specific Goal', 'status' => 'active']);

        $response = $this->getJson("/api/V1/goals/{$goal->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.goal.title', 'Specific Goal');
        $this->assertArrayHasKey('momentum', $response->json('data'));
    }

    /**
     * Test update goal.
     */
    public function test_user_can_update_their_goal(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $goal = Goals::create(['user_id' => $user->id, 'title' => 'Old Title', 'status' => 'active']);

        $response = $this->putJson("/api/V1/goals/{$goal->id}", [
            'title' => 'New Updated Title',
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Updated Title', $goal->fresh()->title);
        $this->assertEquals('completed', $goal->fresh()->status);
    }

    /**
     * Test delete goal.
     */
    public function test_user_can_delete_their_goal(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $goal = Goals::create(['user_id' => $user->id, 'title' => 'To Delete', 'status' => 'active']);

        $response = $this->deleteJson("/api/V1/goals/{$goal->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    /**
     * Test non-measurable goal rejects progress updates.
     */
    public function test_non_measurable_goal_rejects_progress_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $goal = Goals::create(['user_id' => $user->id, 'title' => 'General Growth', 'status' => 'active']);

        $response = $this->postJson("/api/V1/goals/{$goal->id}/progress", [
            'current_value' => 50,
        ]);

        $response->assertStatus(422);
    }
}

