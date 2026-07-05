<?php
// database/factories/FeedTypeFactory.php

namespace Database\Factories;

use App\Models\FeedType;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedTypeFactory extends Factory
{
    protected $model = FeedType::class;

    public function definition(): array
    {
        // Get existing species instead of creating new ones
        $species = Species::inRandomOrder()->first();
        
        // If no species exist, create one (shouldn't happen if SpeciesSeeder ran first)
        if (!$species) {
            $species = Species::create([
                'code' => 'CH',
                'name' => 'Chicken',
                'icon' => 'twemoji:chicken',
                'color_hex' => '#F59E0B',
                'description' => 'Default species',
                'is_active' => true,
            ]);
        }
        
        $categories = ['starter', 'grower', 'finisher', 'layer', 'breeder'];
        
        return [
            'species_id' => $species->id,
            'name' => $this->faker->randomElement([
                'Chicken Starter', 'Chicken Grower', 'Chicken Finisher',
                'Pig Starter', 'Pig Grower', 'Cattle Feed',
                'Rabbit Pellets', 'Goat Feed', 'Sheep Feed'
            ]),
            'code' => strtoupper($this->faker->unique()->lexify('???-?????')),
            'category' => $this->faker->randomElement($categories),
            'protein_percentage' => $this->faker->randomFloat(1, 14, 24),
            'energy_mj_kg' => $this->faker->randomFloat(1, 10, 14),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function chicken(): self
    {
        $species = Species::where('code', 'CH')->first();
        
        return $this->state(fn (array $attributes) => [
            'species_id' => $species ? $species->id : Species::factory()->create(['code' => 'CH'])->id,
        ]);
    }

    public function pig(): self
    {
        $species = Species::where('code', 'PG')->first();
        
        return $this->state(fn (array $attributes) => [
            'species_id' => $species ? $species->id : Species::factory()->create(['code' => 'PG'])->id,
        ]);
    }
}