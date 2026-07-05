<?php
// database/factories/OffspringRecordFactory.php

namespace Database\Factories;

use App\Models\OffspringRecord;
use App\Models\BreedingRecord;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;

class OffspringRecordFactory extends Factory
{
    protected $model = OffspringRecord::class;

    public function definition(): array
    {
        $breedingRecord = BreedingRecord::where('is_successful', true)->inRandomOrder()->first() 
            ?? BreedingRecord::factory()->successful()->create();
        
        return [
            'breeding_record_id' => $breedingRecord->id,
            'new_flock_id' => $this->faker->boolean(30) ? Flock::factory()->create()->id : null,
            'count' => $breedingRecord->offspring_count ?? $this->faker->numberBetween(8, 15),
            'average_birth_weight_kg' => $this->faker->randomFloat(2, 0.8, 2.0),
            'ear_tag_prefix' => $this->faker->randomElement(['PIG-', 'CH-', 'CT-', 'SH-']),
            'ear_tag_start_number' => $this->faker->numberBetween(100, 1000),
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}