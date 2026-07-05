<?php
// database/factories/PerformanceMetricFactory.php

namespace Database\Factories;

use App\Models\PerformanceMetric;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PerformanceMetricFactory extends Factory
{
    protected $model = PerformanceMetric::class;

    public function definition(): array
    {
        $flock = Flock::inRandomOrder()->first() ?? Flock::factory()->create();
        
        return [
            'flock_id' => $flock->id,
            'mortality_rate' => $this->faker->randomFloat(1, 1, 10),
            'feed_conversion_ratio' => $this->faker->randomFloat(2, 1.5, 3.5),
            'average_daily_gain_kg' => $this->faker->randomFloat(3, 0.03, 0.08),
            'total_feed_consumed_kg' => $this->faker->numberBetween(3000, 10000),
            'total_weight_gained_kg' => $this->faker->numberBetween(1500, 5000),
            'total_revenue' => $this->faker->optional(0.7)->numberBetween(10000, 50000),
            'total_cost' => $this->faker->numberBetween(5000, 25000),
            'net_profit' => $this->faker->optional(0.7)->numberBetween(1000, 30000),
            'roi_percentage' => $this->faker->optional(0.7)->randomFloat(2, 10, 100),
            'calculated_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function forClosedFlock(): self
    {
        return $this->state(fn (array $attributes) => [
            'total_revenue' => $this->faker->numberBetween(10000, 50000),
            'net_profit' => $this->faker->numberBetween(1000, 30000),
            'roi_percentage' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    public function forActiveFlock(): self
    {
        return $this->state(fn (array $attributes) => [
            'total_revenue' => null,
            'net_profit' => null,
            'roi_percentage' => null,
        ]);
    }
}