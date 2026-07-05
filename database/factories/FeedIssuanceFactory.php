<?php
// database/factories/FeedIssuanceFactory.php

namespace Database\Factories;

use App\Models\FeedIssuance;
use App\Models\Flock;
use App\Models\FeedDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FeedIssuanceFactory extends Factory
{
    protected $model = FeedIssuance::class;

    public function definition(): array
    {
        return [
            'flock_id' => Flock::factory()->active(),
            'feed_delivery_id' => FeedDelivery::factory(),
            'quantity_kg' => $this->faker->numberBetween(100, 600),
            'issuance_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'issuance_time' => $this->faker->time('H:i:s'),
            'notes' => $this->faker->optional()->sentence(),
            'issued_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // FIX: this method didn't exist before. DatabaseSeeder calls
    // ->forFlock($flock), which Laravel's Factory::__call() was silently
    // intercepting and mishandling — passing the Flock model itself into
    // state()'s array_merge(), causing:
    // "array_merge(): Argument #2 must be of type array, App\Models\Flock given"
    public function forFlock(Flock $flock): self
    {
        return $this->state(fn (array $attributes) => [
            'flock_id' => $flock->id,
        ]);
    }

    public function forToday(): self
    {
        return $this->state(fn (array $attributes) => [
            'issuance_date' => now(),
        ]);
    }

    public function forDate(string $date): self
    {
        return $this->state(fn (array $attributes) => [
            'issuance_date' => $date,
        ]);
    }
}