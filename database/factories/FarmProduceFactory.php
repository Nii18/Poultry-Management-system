<?php

namespace Database\Factories;

use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FarmProduce>
 */
class FarmProduceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $flock = Flock::inRandomOrder()->first() ?? Flock::factory()->create();
        $quantity = $this->faker->numberBetween(80, 120);

        return [
            'flock_id' => $flock->id,
            'product_type' => 'eggs',
            'produce_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'quantity' => $quantity,
            'quantity_damaged' => $this->faker->numberBetween(0, (int) round($quantity * 0.05)),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}