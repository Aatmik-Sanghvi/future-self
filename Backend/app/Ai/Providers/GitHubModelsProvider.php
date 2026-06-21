<?php

namespace App\Ai\Providers;

use App\Ai\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class GitHubModelsProvider implements AIProviderInterface
{
    public function chat(array $messages): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('ai.providers.github.key'),
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'Content-Type' => 'application/json',
        ])->post(
            config('ai.providers.github.url') . '/chat/completions',
            [
                'model' => config('ai.providers.github.model'),
                'messages' => $messages,
            ]
        );

        return $response->json()['choices'][0]['message']['content'] ?? 'No response received.';
    }
}