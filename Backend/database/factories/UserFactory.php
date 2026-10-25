<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'daily_limit' => 10,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pro' => true,
            'subscription_status' => 'active',
            'pro_expires_at' => now()->addMonth(),
            'has_used_trial' => true,
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pro' => true,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
            'pro_expires_at' => now()->addDays(7),
            'has_used_trial' => true,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pro' => false,
            'subscription_status' => 'cancelled',
            'has_used_trial' => true,
        ]);
    }
}
