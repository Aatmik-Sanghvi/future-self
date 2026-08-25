<?php

namespace App\Ai\Agents;

use App\Models\DesiredTraits;
use App\Models\Fear;
use App\Models\Goals;
use App\Models\RoleModel;
use App\Models\CommunicationTone;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class OnboardingSummary implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $goals = Goals::where('user_id', auth()->id())->latest()->first();
        $goalId = $goals?->id;

        $fears = $goalId ? Fear::where('goal_id', $goalId)->first() : null;
        $desiredTraits = $goalId ? implode(", ", DesiredTraits::where('goal_id', $goalId)->pluck('trait')->toArray()) : '';
        $roleModels = $goalId ? implode(", ", RoleModel::where('goal_id', $goalId)->pluck('names')->toArray()) : '';
        $tone = $goalId ? CommunicationTone::where('goal_id', $goalId)->first() : null;

        return "You are an expert behavioral analyst.
            Create a compact, insightful onboarding summary (100-120 words) for the user's Future Self AI mentor.

            Input Data:
            - Goals: {$goals?->title} - {$goals?->description} (Category: {$goals?->category}, Timeframe: {$goals?->timeframe}, Priority: {$goals?->priority})
            - Fears: {$fears?->fear} (Category: {$fears?->category}, Priority: {$fears?->priority})
            - Desired Traits: {$desiredTraits}
            - Role Models: {$roleModels}
            - Preferred Tone: {$tone?->tone}

            Instructions:
            - Synthesize the user's aspirations, internal obstacles, admired values, and preferred tone into a unified third-person narrative ('The user...').
            - Keep it warm, objective, actionable, and under 120 words.
            - Output only the final summary in the feedback field.";
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
            'feedback' => $schema->string()->required(),
        ];
    }
}
