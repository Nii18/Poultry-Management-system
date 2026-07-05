<?php
// database/factories/SpeciesFactory.php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        // Try to get an existing species first
        $existingSpecies = Species::inRandomOrder()->first();
        
        if ($existingSpecies) {
            return [
                'code' => $existingSpecies->code,
                'name' => $existingSpecies->name,
                'icon' => $existingSpecies->icon,
                'color_hex' => $existingSpecies->color_hex,
                'description' => $existingSpecies->description,
                'default_metrics' => $existingSpecies->default_metrics,
                'growth_standards' => $existingSpecies->growth_standards,
                'health_indicators' => $existingSpecies->health_indicators,
                'gestation_days' => $existingSpecies->gestation_days,
                'weaning_days' => $existingSpecies->weaning_days,
                'market_age_days' => $existingSpecies->market_age_days,
                'market_weight_kg' => $existingSpecies->market_weight_kg,
                'lifespan_years' => $existingSpecies->lifespan_years,
                'sexual_maturity_days' => $existingSpecies->sexual_maturity_days,
                'is_active' => $existingSpecies->is_active,
                'created_at' => $existingSpecies->created_at ?? now(),
                'updated_at' => $existingSpecies->updated_at ?? now(),
            ];
        }

        // Fallback: Define species data (only used if no species exist)
        $species = [
            'Chicken' => ['code' => 'CH', 'icon' => 'twemoji:chicken', 'color' => '#F59E0B'],
            'Pig' => ['code' => 'PG', 'icon' => 'mdi:pig', 'color' => '#EC489A'],
            'Cattle' => ['code' => 'CT', 'icon' => 'mdi:cow', 'color' => '#10B981'],
            'Rabbit' => ['code' => 'RB', 'icon' => 'mdi:rabbit', 'color' => '#A855F7'],
            'Goat' => ['code' => 'GT', 'icon' => 'twemoji:goat', 'color' => '#84CC16'],
            'Turkey' => ['code' => 'TK', 'icon' => 'mdi:turkey', 'color' => '#EF4444'],
            'Fish' => ['code' => 'FS', 'icon' => 'mdi:fish', 'color' => '#3B82F6'],
            'Sheep' => ['code' => 'SH', 'icon' => 'mdi:sheep', 'color' => '#8B5CF6'],
        ];

        $name = $this->faker->randomElement(array_keys($species));
        $data = $species[$name];

        return [
            'code' => $data['code'],
            'name' => $name,
            'icon' => $data['icon'],
            'color_hex' => $data['color'],
            'description' => $this->faker->sentence(),
            'default_metrics' => json_encode([
                'fcr_target' => $this->faker->randomFloat(1, 1.5, 6.5),
                'mortality_target' => $this->faker->numberBetween(3, 15),
            ]),
            'growth_standards' => json_encode($this->generateGrowthStandards($name)),
            'health_indicators' => json_encode([
                'normal_temperature' => $this->faker->randomFloat(1, 38, 42),
                'normal_heart_rate' => $this->faker->numberBetween(60, 250),
                'normal_respiration' => $this->faker->numberBetween(15, 55),
            ]),
            'gestation_days' => $this->faker->optional(0.7)->numberBetween(21, 283),
            'weaning_days' => $this->faker->optional(0.5)->numberBetween(21, 210),
            'market_age_days' => $this->faker->numberBetween(42, 730),
            'market_weight_kg' => $this->faker->randomFloat(1, 1, 550),
            'lifespan_years' => $this->faker->numberBetween(5, 20),
            'sexual_maturity_days' => $this->faker->numberBetween(140, 540),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateGrowthStandards(string $species): array
    {
        $standards = [];
        
        switch ($species) {
            case 'Chicken':
                for ($week = 1; $week <= 6; $week++) {
                    $standards["week{$week}"] = round(0.18 * $week + ($week * 0.05), 2);
                }
                break;
            case 'Pig':
                for ($month = 1; $month <= 6; $month++) {
                    $standards["month{$month}"] = round(10 * $month + ($month * 5), 1);
                }
                break;
            default:
                for ($i = 1; $i <= 6; $i++) {
                    $standards["period{$i}"] = round($this->faker->randomFloat(1, 1, 50), 1);
                }
        }
        
        return $standards;
    }
}