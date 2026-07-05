<?php
// database/factories/DailyLogFactory.php

namespace Database\Factories;

use App\Models\DailyLog;
use App\Models\Flock;
use App\Models\FarmProduce;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class DailyLogFactory extends Factory
{
    protected $model = DailyLog::class;

    public function definition(): array
    {
        $flock = Flock::inRandomOrder()->first() ?? Flock::factory()->create();
        $logDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $eggsCollected = $this->faker->numberBetween(80, 120);
        $eggsDamaged = $this->faker->numberBetween(0, (int) round($eggsCollected * 0.05));
        
        return [
            'flock_id' => $flock->id,
            'log_date' => $logDate,
            'mortality_count' => $this->faker->numberBetween(0, 5),
            'culling_count' => $this->faker->numberBetween(0, 2),
            'eggs_collected' => $eggsCollected,
            'eggs_damaged' => $eggsDamaged,
            'feed_intake_kg' => $this->faker->numberBetween(450, 550),
            'water_consumption_liters' => $this->faker->numberBetween(900, 1100),
            'average_weight_kg' => $this->faker->randomFloat(2, 1.8, 3.5),
            'min_temperature_c' => $this->faker->numberBetween(28, 30),
            'max_temperature_c' => $this->faker->numberBetween(31, 34),
            'min_humidity' => $this->faker->numberBetween(55, 65),
            'max_humidity' => $this->faker->numberBetween(66, 75),
            'ammonia_ppm' => $this->faker->numberBetween(5, 15),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // FIX: this method didn't exist before. Both DatabaseSeeder and
    // FlockFactory::withFullHistory() call ->forFlock($flock), which was
    // being mishandled by Laravel's Factory::__call() magic method,
    // ultimately causing the array_merge() TypeError during seeding.
    public function forFlock(Flock $flock): self
    {
        return $this->state(fn (array $attributes) => [
            'flock_id' => $flock->id,
        ]);
    }

    public function withProduce(?int $createdBy = null): self
{
    return $this->afterCreating(function (DailyLog $log) use ($createdBy) {
        FarmProduce::factory()->create([
            'flock_id' => $log->flock_id,
            'product_type' => 'eggs',
            'produce_date' => $log->log_date,
            'quantity' => $log->eggs_collected,
            'quantity_damaged' => $log->eggs_damaged,
            'created_by' => $createdBy ?? $log->created_by,
        ]);
    });
}
}