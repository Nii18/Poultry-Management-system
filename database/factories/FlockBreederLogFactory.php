<?php
// database/factories/FlockBreederLogFactory.php

namespace Database\Factories;

use App\Models\FlockBreederLog;
use App\Models\Flock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlockBreederLogFactory extends Factory
{
    protected $model = FlockBreederLog::class;

    public function definition(): array
    {
        return [
            'flock_id' => Flock::factory()->breedingStock(),
            'breeder_count' => $this->faker->numberBetween(5, 50),
            'sellable_count' => $this->faker->numberBetween(0, 10),
            'reason' => $this->faker->randomElement([
                'Initial breeding stock count',
                'After culling',
                'New breeding stock added',
                'Updated count',
                'During health check',
                'After weaning',
                'Breeding group consolidation'
            ]),
            'set_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a specific breeder count scenario
     */
    public function initialCount(): self
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'Initial breeding stock count',
        ]);
    }

    /**
     * Create an update after culling
     */
    public function afterCulling(): self
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'After culling',
            'breeder_count' => $this->faker->numberBetween(10, 30),
            'sellable_count' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * Create a log with a specific count
     */
    public function withCount(int $breederCount, int $sellableCount = 0): self
    {
        return $this->state(fn (array $attributes) => [
            'breeder_count' => $breederCount,
            'sellable_count' => $sellableCount,
        ]);
    }
}