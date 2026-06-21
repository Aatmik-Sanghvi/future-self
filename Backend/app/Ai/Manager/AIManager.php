<?php

namespace App\Ai\Manager;

use App\Ai\Providers\GitHubModelsProvider;
use InvalidArgumentException;

class AIManager
{
    public function provider()
    {
        return match (config('ai.default')) {
            'github' => new GitHubModelsProvider(),
            default => throw new InvalidArgumentException("Unsupported AI provider: ".config('ai.default')),
        };
    }
}