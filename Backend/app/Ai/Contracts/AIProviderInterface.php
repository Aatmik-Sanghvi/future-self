<?php

namespace App\Ai\Contracts;

interface AIProviderInterface
{
    public function chat(array $messages): string;
}
