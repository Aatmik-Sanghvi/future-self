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
        $goals = Goals::where('user_id',auth()->id())->first();
        $fears = Fear::where('goal_id',$goals->id)->first();
        $desiredTraits = implode(", ",DesiredTraits::where('goal_id',$goals->id)->pluck('trait')->toArray());
        $roleModels = implode(", ",RoleModel::where('goal_id',$goals->id)->pluck('names')->toArray());
        $tone = CommunicationTone::where('goal_id',$goals->id)->first();

        return "
            You are an expert psychologist, life coach, and behavioral analyst.

            Your task is to create a personalized onboarding summary based ONLY on the information provided by the user.

            The summary will later be used by an AI that acts as the user's future self, so it should help the AI deeply understand the user's motivations, personality, aspirations, and communication preferences.

            Input:

            Goals:
            Title - {{$goals->title}}
            Description - {{$goals->description}}
            Category - {{$goals->category}}
            Timeframe - {{$goals->timeframe}}
            Priority - {{$goals->priority}}

            Biggest Fears:
            Fear - {{$fears->fear}}
            Category - {{$fears->category}}
            Priority - {{$fears->priority}}

            Desired Traits:
            {{$desiredTraits}}

            Role Models:
            {{$roleModels}}

            Preferred Tone:
            {{$tone->tone}}

            Instructions:

            - Analyze all of the user's answers together rather than summarizing each section separately.
            - Infer motivations and personality only when they are strongly supported by the provided information.
            - Do not invent facts or make unsupported assumptions.
            - Focus on who this person wants to become.
            - Explain what appears to motivate them.
            - Identify the internal obstacles suggested by their fears.
            - Describe how their desired traits complement their goals.
            - Explain what can be learned from the role models they admire.
            - Mention the preferred communication style naturally.
            - Keep the summary warm, insightful, and objective.
            - Write in third person ('The user...'), not first person.
            - Do not address the user directly.
            - Avoid bullet points.
            - Keep the response between 200 and 350 words.

            The summary should help another AI instantly understand:
            - What drives this person.
            - What they are trying to achieve.
            - What holds them back.
            - How they like to be supported.
            - What kind of guidance will be most effective.

            Output only the summary.
        ";
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
