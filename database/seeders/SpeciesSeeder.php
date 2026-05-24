<?php
// database/seeders/SpeciesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Species;

class SpeciesSeeder extends Seeder
{
    public function run()
    {
        // Chicken
        Species::updateOrCreate(
            ['code' => 'CH'],
            [
                'name' => 'Chicken',
                'icon' => 'twemoji:chicken',
                'color_hex' => '#F59E0B',
                'description' => 'Poultry raised for meat and eggs',
                'default_metrics' => json_encode([
                    'fcr_target' => 1.8,
                    'mortality_target' => 5,
                    'egg_production_target' => 85
                ]),
                'growth_standards' => json_encode([
                    'week1' => 0.18,
                    'week2' => 0.45,
                    'week3' => 0.85,
                    'week4' => 1.35,
                    'week5' => 1.95,
                    'week6' => 2.5
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 41.5,
                    'normal_heart_rate' => 250,
                    'normal_respiration' => 20
                ]),
                'gestation_days' => 21,
                'weaning_days' => null,
                'market_age_days' => 42,
                'market_weight_kg' => 2.5,
                'lifespan_years' => 8,
                'sexual_maturity_days' => 140,
                'is_active' => true,
            ]
        );

        // Pig
        Species::updateOrCreate(
            ['code' => 'PG'],
            [
                'name' => 'Pig',
                'icon' => 'mdi:pig',
                'color_hex' => '#EC489A',
                'description' => 'Swine raised for pork production',
                'default_metrics' => json_encode([
                    'fcr_target' => 3.2,
                    'mortality_target' => 8,
                    'backfat_target' => 12
                ]),
                'growth_standards' => json_encode([
                    'month1' => 10,
                    'month2' => 25,
                    'month3' => 45,
                    'month4' => 70,
                    'month5' => 95,
                    'month6' => 115
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 39.0,
                    'normal_heart_rate' => 70,
                    'normal_respiration' => 15
                ]),
                'gestation_days' => 114,
                'weaning_days' => 21,
                'market_age_days' => 180,
                'market_weight_kg' => 115,
                'lifespan_years' => 15,
                'sexual_maturity_days' => 240,
                'is_active' => true,
            ]
        );

        // Cattle
        Species::updateOrCreate(
            ['code' => 'CT'],
            [
                'name' => 'Cattle',
                'icon' => 'mdi:cow',
                'color_hex' => '#10B981',
                'description' => 'Bovine raised for beef and dairy',
                'default_metrics' => json_encode([
                    'fcr_target' => 6.5,
                    'mortality_target' => 3,
                    'milk_target_liters' => 25
                ]),
                'growth_standards' => json_encode([
                    'month3' => 100,
                    'month6' => 200,
                    'month12' => 350,
                    'month18' => 450,
                    'month24' => 550
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 38.5,
                    'normal_heart_rate' => 60,
                    'normal_respiration' => 25
                ]),
                'gestation_days' => 283,
                'weaning_days' => 210,
                'market_age_days' => 730,
                'market_weight_kg' => 550,
                'lifespan_years' => 20,
                'sexual_maturity_days' => 540,
                'is_active' => true,
            ]
        );

        // Rabbit
        Species::updateOrCreate(
            ['code' => 'RB'],
            [
                'name' => 'Rabbit',
                'icon' => 'mdi:rabbit',
                'color_hex' => '#A855F7',
                'description' => 'Rabbits raised for meat and fur',
                'default_metrics' => json_encode([
                    'fcr_target' => 3.0,
                    'mortality_target' => 10,
                    'litter_size_target' => 8
                ]),
                'growth_standards' => json_encode([
                    'week4' => 0.8,
                    'week8' => 1.8,
                    'week12' => 2.5
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 38.5,
                    'normal_heart_rate' => 200,
                    'normal_respiration' => 55
                ]),
                'gestation_days' => 31,
                'weaning_days' => 28,
                'market_age_days' => 84,
                'market_weight_kg' => 2.5,
                'lifespan_years' => 9,
                'sexual_maturity_days' => 180,
                'is_active' => true,
            ]
        );

        // Goat
        Species::updateOrCreate(
            ['code' => 'GT'],
            [
                'name' => 'Goat',
                'icon' => 'twemoji:goat',
                'color_hex' => '#84CC16',
                'description' => 'Goats raised for meat, milk, and fiber',
                'default_metrics' => json_encode([
                    'fcr_target' => 5.0,
                    'mortality_target' => 5,
                    'milk_target_liters' => 3
                ]),
                'growth_standards' => json_encode([
                    'month3' => 15,
                    'month6' => 25,
                    'month12' => 35
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 39.0,
                    'normal_heart_rate' => 80,
                    'normal_respiration' => 20
                ]),
                'gestation_days' => 150,
                'weaning_days' => 90,
                'market_age_days' => 365,
                'market_weight_kg' => 35,
                'lifespan_years' => 15,
                'sexual_maturity_days' => 270,
                'is_active' => true,
            ]
        );

        // Turkey
        Species::updateOrCreate(
            ['code' => 'TK'],
            [
                'name' => 'Turkey',
                'icon' => 'mdi:turkey',
                'color_hex' => '#EF4444',
                'description' => 'Turkeys raised for meat production',
                'default_metrics' => json_encode([
                    'fcr_target' => 2.5,
                    'mortality_target' => 7,
                    'meat_yield_target' => 75
                ]),
                'growth_standards' => json_encode([
                    'week4' => 1.5,
                    'week8' => 4.5,
                    'week12' => 8.0,
                    'week16' => 11.0,
                    'week20' => 14.0
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 41.0,
                    'normal_heart_rate' => 160,
                    'normal_respiration' => 28
                ]),
                'gestation_days' => 28,
                'weaning_days' => null,
                'market_age_days' => 140,
                'market_weight_kg' => 14,
                'lifespan_years' => 10,
                'sexual_maturity_days' => 210,
                'is_active' => true,
            ]
        );

        // Fish
        Species::updateOrCreate(
            ['code' => 'FS'],
            [
                'name' => 'Fish',
                'icon' => 'mdi:fish',
                'color_hex' => '#3B82F6',
                'description' => 'Fish raised in aquaculture systems',
                'default_metrics' => json_encode([
                    'fcr_target' => 1.5,
                    'mortality_target' => 15,
                    'water_quality_target' => 95
                ]),
                'growth_standards' => json_encode([
                    'month3' => 0.2,
                    'month6' => 0.5,
                    'month9' => 0.8,
                    'month12' => 1.0
                ]),
                'health_indicators' => json_encode([
                    'optimal_ph' => 7.0,
                    'optimal_temp_c' => 26,
                    'optimal_dissolved_oxygen' => 6
                ]),
                'gestation_days' => null,
                'weaning_days' => null,
                'market_age_days' => 270,
                'market_weight_kg' => 1.0,
                'lifespan_years' => 5,
                'sexual_maturity_days' => 365,
                'is_active' => true,
            ]
        );

        // Sheep
        Species::updateOrCreate(
            ['code' => 'SH'],
            [
                'name' => 'Sheep',
                'icon' => 'mdi:sheep',
                'color_hex' => '#8B5CF6',
                'description' => 'Sheep raised for meat, milk, and wool',
                'default_metrics' => json_encode([
                    'fcr_target' => 5.5,
                    'mortality_target' => 6,
                    'wool_yield_target' => 4
                ]),
                'growth_standards' => json_encode([
                    'month3' => 20,
                    'month6' => 35,
                    'month12' => 55,
                    'month18' => 70
                ]),
                'health_indicators' => json_encode([
                    'normal_temperature' => 39.0,
                    'normal_heart_rate' => 80,
                    'normal_respiration' => 20
                ]),
                'gestation_days' => 147,
                'weaning_days' => 90,
                'market_age_days' => 365,
                'market_weight_kg' => 45,
                'lifespan_years' => 12,
                'sexual_maturity_days' => 270,
                'is_active' => true,
            ]
        );

        $this->command->info('Species seeded successfully with Iconify icons!');
    }
}