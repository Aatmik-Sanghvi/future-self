<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\DailyMission;
use App\Models\Goals;
use App\Services\GoalMomentumService;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        protected GoalMomentumService $momentumService
    ) {}

    /**
     * Get the primary/active Goal Tracking overview for the authenticated user.
     */
    public function tracking(Request $request)
    {
        $user = auth()->user();

        // Optional goal_id query param to inspect a specific goal, else active/latest goal
        $goalId = $request->query('goal_id');

        $goalQuery = Goals::where('user_id', $user->id);
        if ($goalId) {
            $goal = $goalQuery->find($goalId);
            if (!$goal) {
                return ResponseHelper::send(404, 'Specified goal not found.');
            }
        } else {
            $goal = (clone $goalQuery)->where('status', 'active')->latest()->first()
                ?? $goalQuery->latest()->first();
        }

        if (!$goal) {
            return ResponseHelper::send(200, 'No goals configured yet.', [
                'has_goal' => false,
                'goal' => null,
                'momentum' => null,
                'goals' => [],
            ]);
        }

        // Calculate Goal Momentum
        $momentum = $this->momentumService->calculate($goal);

        // Fetch recent missions for this goal
        $recentMissions = DailyMission::where('goal_id', $goal->id)
            ->orderByDesc('mission_date')
            ->limit(10)
            ->get();

        // List all user goals with quick summary
        $allGoals = Goals::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($g) use ($goal) {
                return [
                    'id' => $g->id,
                    'title' => $g->title,
                    'category' => $g->category,
                    'timeframe' => $g->timeframe,
                    'priority' => $g->priority,
                    'status' => $g->status,
                    'is_selected' => $g->id === $goal->id,
                    'is_measurable' => $g->isMeasurable(),
                    'target' => $g->getTargetProgress(),
                ];
            });

        return ResponseHelper::send(200, 'Goal tracking data fetched successfully.', [
            'has_goal' => true,
            'goal' => $goal,
            'momentum' => $momentum,
            'recent_missions' => $recentMissions,
            'all_goals' => $allGoals,
        ]);
    }

    /**
     * List all goals for the authenticated user with momentum summary.
     */
    public function index()
    {
        $user = auth()->user();
        $goals = Goals::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $goalsWithMomentum = $goals->map(function ($goal) {
            $momentum = $this->momentumService->calculate($goal);
            return [
                'goal' => $goal,
                'momentum_score' => $momentum['score'],
                'momentum_status' => $momentum['status'],
                'completion_rate' => $momentum['mission_completion_rate'],
                'missions_completed' => $momentum['missions_completed'],
                'focus_minutes' => $momentum['focus_minutes'],
                'recent_trend' => $momentum['recent_trend'],
                'target' => $goal->getTargetProgress(),
            ];
        });

        return ResponseHelper::send(200, 'Goals fetched successfully.', $goalsWithMomentum);
    }

    /**
     * Get a specific goal and its detailed tracking metrics.
     */
    public function show($id)
    {
        $user = auth()->user();
        $goal = Goals::where('user_id', $user->id)->find($id);

        if (!$goal) {
            return ResponseHelper::send(404, 'Goal not found.');
        }

        $momentum = $this->momentumService->calculate($goal);

        $recentMissions = DailyMission::where('goal_id', $goal->id)
            ->orderByDesc('mission_date')
            ->limit(20)
            ->get();

        return ResponseHelper::send(200, 'Goal details fetched successfully.', [
            'goal' => $goal,
            'momentum' => $momentum,
            'recent_missions' => $recentMissions,
        ]);
    }

    /**
     * Create a new goal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'timeframe' => 'nullable|string|in:short-term,long-term',
            'priority' => 'nullable|integer|between:1,5',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,completed,dropped',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['priority'] = $validated['priority'] ?? 3;
        $validated['status'] = $validated['status'] ?? 'active';

        $goal = Goals::create($validated);
        $momentum = $this->momentumService->calculate($goal);

        return ResponseHelper::send(201, 'Goal created successfully.', [
            'goal' => $goal,
            'momentum' => $momentum,
        ]);
    }

    /**
     * Update an existing goal.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $goal = Goals::where('user_id', $user->id)->find($id);

        if (!$goal) {
            return ResponseHelper::send(404, 'Goal not found.');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'timeframe' => 'nullable|string|in:short-term,long-term',
            'priority' => 'nullable|integer|between:1,5',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,completed,dropped',
        ]);

        $goal->update($validated);
        $momentum = $this->momentumService->calculate($goal->fresh());

        return ResponseHelper::send(200, 'Goal updated successfully.', [
            'goal' => $goal->fresh(),
            'momentum' => $momentum,
        ]);
    }

    /**
     * Delete a goal.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $goal = Goals::where('user_id', $user->id)->find($id);

        if (!$goal) {
            return ResponseHelper::send(404, 'Goal not found.');
        }

        $goal->delete();

        return ResponseHelper::send(200, 'Goal deleted successfully.');
    }

    /**
     * Update measurable target progress for measurable goals.
     */
    public function updateProgress(Request $request, $id)
    {
        $user = auth()->user();
        $goal = Goals::where('user_id', $user->id)->find($id);

        if (!$goal) {
            return ResponseHelper::send(404, 'Goal not found.');
        }

        if (!$goal->isMeasurable()) {
            return ResponseHelper::send(422, 'This goal does not have a measurable target configured.');
        }

        $request->validate([
            'current_value' => 'required_without:increment|nullable|numeric|min:0',
            'increment' => 'nullable|numeric',
        ]);

        if ($request->has('current_value') && !is_null($request->input('current_value'))) {
            $goal->current_value = (float) $request->input('current_value');
        } elseif ($request->has('increment')) {
            $goal->current_value = max(0, (float) ($goal->current_value ?? 0) + (float) $request->input('increment'));
        }

        // Auto-mark as completed if target reached
        if ($goal->target_value > 0 && $goal->current_value >= $goal->target_value && $goal->status === 'active') {
            $goal->status = 'completed';
        }

        $goal->save();
        $momentum = $this->momentumService->calculate($goal->fresh());

        return ResponseHelper::send(200, 'Goal progress updated successfully.', [
            'goal' => $goal->fresh(),
            'momentum' => $momentum,
            'target' => $goal->fresh()->getTargetProgress(),
        ]);
    }
}
