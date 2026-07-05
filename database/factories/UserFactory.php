<?php
// database/factories/UserFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => '02' . $this->faker->numberBetween(10000000, 99999999),
            'role' => $this->faker->randomElement(['admin', 'manager', 'worker', 'veterinarian', 'accountant']),
            'farm_name' => $this->faker->randomElement(['Main Farm', 'Green Valley Farm', 'Sunrise Poultry', 'Golden Acres']),
            'is_active' => $this->faker->boolean(90),
            'last_seen_at' => $this->faker->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'last_login_at' => $this->faker->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'last_activity_at' => $this->faker->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function worker(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'worker',
            'is_active' => true,
        ]);
    }

    public function manager(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_seen_at' => $this->faker->dateTimeBetween('-60 days', '-31 days'),
        ]);
    }

    public function online(): self
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => now(),
            'last_login_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    public function neverLoggedIn(): self
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => null,
            'last_login_at' => null,
            'last_activity_at' => null,
        ]);
    }
}