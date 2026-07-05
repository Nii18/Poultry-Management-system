<?php
// database/factories/BreedingRecordFactory.php

namespace Database\Factories;

use App\Models\BreedingRecord;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreedingRecordFactory extends Factory
{
    protected $model = BreedingRecord::class;

    public function definition(): array
    {
        $damFlock = Flock::where('is_breeding_stock', true)->where('sex', 'female')->inRandomOrder()->first() 
            ?? Flock::factory()->femaleBreeding()->create();
        
        $sireFlock = Flock::where('is_breeding_stock', true)->where('sex', 'male')->inRandomOrder()->first()
            ?? Flock::factory()->maleBreeding()->create();
        
        $breedingDate = $this->faker->dateTimeBetween('-120 days', '-30 days');
        $isSuccessful = $this->faker->boolean(75);
        $method = $this->faker->randomElement(['natural', 'artificial_insemination']);
        
        $expectedDelivery = Carbon::parse($breedingDate)->addDays($this->faker->numberBetween(110, 120));
        
        return [
            'flock_id' => $damFlock->id,
            'mate_id' => $method === 'natural' ? $sireFlock->id : null,
            'female_breeder_count' => $this->faker->numberBetween(10, 30),
            'male_breeder_count' => $method === 'natural' ? $this->faker->numberBetween(2, 5) : null,
            'breeding_date' => $breedingDate,
            'expected_delivery_date' => $expectedDelivery,
            'actual_delivery_date' => $isSuccessful ? Carbon::parse($expectedDelivery)->addDays($this->faker->numberBetween(-3, 3)) : null,
            'breeding_method' => $method,
            'is_successful' => $isSuccessful,
            'offspring_count' => $isSuccessful ? $this->faker->numberBetween(8, 15) : 0,
            'stillborn_count' => $isSuccessful ? $this->faker->numberBetween(0, 3) : 0,
            'weaned_count' => $isSuccessful ? $this->faker->numberBetween(6, 12) : 0,
            'notes' => $this->faker->optional()->sentence(),
            'recorded_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function successful(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_successful' => true,
            'offspring_count' => $this->faker->numberBetween(8, 15),
            'actual_delivery_date' => Carbon::parse($attributes['expected_delivery_date'])->addDays($this->faker->numberBetween(-3, 3)),
        ]);
    }

    public function artificial(): self
    {
        return $this->state(fn (array $attributes) => [
            'breeding_method' => 'artificial_insemination',
            'mate_id' => null,
            'male_breeder_count' => null,
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_successful' => false,
            'actual_delivery_date' => null,
            'offspring_count' => 0,
            'stillborn_count' => 0,
            'weaned_count' => 0,
            'expected_delivery_date' => Carbon::now()->addDays($this->faker->numberBetween(30, 60)),
        ]);
    }
}