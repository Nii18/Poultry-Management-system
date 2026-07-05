<?php
// database/factories/HouseFactory.php

namespace Database\Factories;

use App\Models\House;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class HouseFactory extends Factory
{
    protected $model = House::class;

    public function definition(): array
    {
        $species = Species::inRandomOrder()->first();

        if (!$species) {
            $species = Species::create([
                'code'        => 'CH',
                'name'        => 'Chicken',
                'icon'        => 'twemoji:chicken',
                'color_hex'   => '#F59E0B',
                'description' => 'Default species',
                'is_active'   => true,
            ]);
        }

        return [
            // Widened to 3 digits (H001–H999) to avoid OverflowException
            // when seeding creates more than 99 houses.
            'house_code'     => sprintf('H%03d', $this->faker->unique()->numberBetween(1, 999)),
            'name'           => $this->faker->randomElement([
                                    'Broiler House', 'Layer House', 'Pig Barn',
                                    'Cattle Shed', 'Rabbit House', 'Goat Barn',
                                    'Turkey House', 'Fish Pond',
                                ]) . ' ' . strtoupper($this->faker->randomLetter()),
            'species_id'     => $species->id,
            'capacity'       => $this->faker->numberBetween(50, 5000),
            'length_m'       => $this->faker->numberBetween(10, 80),
            'width_m'        => $this->faker->numberBetween(5, 30),
            'height_m'       => $this->faker->randomFloat(1, 2.5, 5),
            'feeders_count'  => $this->faker->numberBetween(5, 30),
            'drinkers_count' => $this->faker->numberBetween(10, 40),
            'fans_count'     => $this->faker->numberBetween(2, 12),
            'heaters_count'  => $this->faker->numberBetween(0, 6),
            'status'         => $this->faker->randomElement(['active', 'maintenance', 'inactive']),
            'notes'          => $this->faker->optional()->sentence(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function chicken(): self
    {
        $species = Species::where('code', 'CH')->first();

        return $this->state(fn (array $attributes) => [
            'species_id' => $species?->id ?? Species::factory()->create(['code' => 'CH'])->id,
        ]);
    }

    public function pig(): self
    {
        $species = Species::where('code', 'PG')->first();

        return $this->state(fn (array $attributes) => [
            'species_id' => $species?->id ?? Species::factory()->create(['code' => 'PG'])->id,
        ]);
    }
}