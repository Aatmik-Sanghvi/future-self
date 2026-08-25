<?php

namespace App\Ai\Agents;

use App\Models\DailyMission;
use App\Models\Goals;
use App\Models\Mood;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class DailyMissionAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public ?User $user = null,
        public ?string $moodType = null,
    ) {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $userId = $this->user?->id ?? auth()->id();
        $goal = Goals::where('user_id', $userId)->latest()->first();

        // Get mood if not passed
        $currentMood = $this->moodType;
        if (! $currentMood && $userId) {
            $latestMood = Mood::where('user_id', $userId)->whereDate('created_at', today())->latest()->first();
            $currentMood = $latestMood?->mood_type ?? 'neutral';
        }

        // Get past 4 missions to avoid repetition & ensure progression
        $pastMissions = DailyMission::where('user_id', $userId)
            ->whereDate('mission_date', '<', today())
            ->orderByDesc('mission_date')
            ->limit(4)
            ->get(['title', 'status', 'difficulty'])
            ->map(fn ($m) => "{$m->title} ({$m->status})")
            ->implode('; ');

        $pastContext = $pastMissions ? "Recent Past Missions: {$pastMissions}" : "Recent Past Missions: None (First mission)";
        $goalTitle = $goal?->title ?? 'General Self Improvement';
        $goalCategory = $goal?->category ?? 'Growth';
        $goalDesc = $goal?->description ?? '';

        return "You are FutureYou, the wiser, caring future self of {$this->user?->name}.
Generate ONE single micro-mission for today that advances their goal while fitting their mood/energy level.

Rules:
- Mood calibration:
  * 'exhausted' / 'sad': Easy task (10-15 mins), gentle small win or clarity action to build confidence without overwhelm.
  * 'neutral': Medium task (15-20 mins), solid focused progress.
  * 'happy' / 'great': Medium or Hard task (20-30 mins), ambitious high-impact milestone.
- Avoid repeating recent past missions. Ensure high actionability (can be done in 1 sitting today).
- Future self note must be warm, encouraging, 1-2 sentences maximum, speaking directly as their future self.
- Keep text concise to preserve tokens.

User Context:
- Goal: {$goalTitle} ({$goalCategory}) {$goalDesc}
- Today's Mood: {$currentMood}
- {$pastContext}";
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'future_self_note' => $schema->string()->required(),
            'category' => $schema->string()->required(),
            'estimated_minutes' => $schema->integer()->required(),
            'difficulty' => $schema->string()->enum(['easy', 'medium', 'hard'])->required(),
        ];
    }
}
