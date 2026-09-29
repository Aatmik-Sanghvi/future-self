<?php

namespace App\Ai\Agents;

use App\Models\DailyMission;
use App\Models\Goals;
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

class MotivationalMailAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public ?User $user = null,
        public int $inactiveDays = 3,
    ) {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $userId = $this->user?->id ?? auth()->id();
        $goal = Goals::where('user_id', $userId)->where('status', 'active')->latest()->first();

        // Get recent mission history for context
        $recentMissions = DailyMission::where('user_id', $userId)
            ->orderByDesc('mission_date')
            ->limit(7)
            ->get(['title', 'status', 'mission_date'])
            ->map(fn ($m) => "{$m->title} ({$m->status} - {$m->mission_date->format('M d')})")
            ->implode('; ');

        $missionContext = $recentMissions ?: 'No recent mission data';
        $goalTitle = $goal?->title ?? 'General Self Improvement';
        $goalDesc = $goal?->description ?? '';
        $userName = $this->user?->name ?? 'there';
        $streak = $this->user?->daily_streak ?? 0;

        return "You are FutureYou — the user's wiser, calmer, emotionally mature future self.
You are writing a personal motivational email to {$userName} who hasn't completed their daily missions for {$this->inactiveDays} consecutive days.

YOUR GOAL: Write a warm, genuine email that motivates them to come back and take action on their goals without guilt-tripping, shaming, or sounding like generic motivational spam.

CRITICAL RULES:
- Write as their future self who deeply cares about them
- Be emotionally intelligent — acknowledge that life gets busy, energy dips, and that's okay
- Don't guilt-trip or use shame tactics
- Don't sound like a corporate newsletter or generic self-help
- Don't use excessive emojis or cringe motivational quotes
- Keep it personal, warm, and grounded
- Include one specific, small actionable step they can take TODAY
- Reference their goal naturally if available
- The tone should feel like a caring older version of them checking in
- Keep the email concise (3-4 short paragraphs max)

USER CONTEXT:
- Name: {$userName}
- Goal: {$goalTitle} {$goalDesc}
- Days inactive: {$this->inactiveDays}
- Previous streak: {$streak} days
- Recent mission history: {$missionContext}

OUTPUT: Generate a subject line and email body. The subject line should feel personal and non-spammy (avoid ALL CAPS, excessive punctuation, or urgency triggers). The body should be the email content in plain text format.";
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
            'subject' => $schema->string()->required(),
            'greeting' => $schema->string()->required(),
            'body' => $schema->string()->required(),
            'actionable_step' => $schema->string()->required(),
            'closing' => $schema->string()->required(),
        ];
    }
}
