<?php
// database/seeders/FeedTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedType;
use App\Models\Species;

class FeedTypeSeeder extends Seeder
{
    public function run()
    {
        $chickenId = Species::where('code', 'CH')->first()->id;
        $pigId = Species::where('code', 'PG')->first()->id;

        FeedType::updateOrCreate(
            ['code' => 'CH-STARTER'],
            [
                'species_id' => $chickenId,
                'name' => 'Chicken Starter',
                'category' => 'starter',
                'protein_percentage' => 22,
                'energy_mj_kg' => 12.5,
                'is_active' => true,
            ]
        );

        FeedType::updateOrCreate(
            ['code' => 'CH-GROWER'],
            [
                'species_id' => $chickenId,
                'name' => 'Chicken Grower',
                'category' => 'grower',
                'protein_percentage' => 20,
                'energy_mj_kg' => 12.8,
                'is_active' => true,
            ]
        );

        FeedType::updateOrCreate(
            ['code' => 'CH-FINISHER'],
            [
                'species_id' => $chickenId,
                'name' => 'Chicken Finisher',
                'category' => 'finisher',
                'protein_percentage' => 18,
                'energy_mj_kg' => 13.0,
                'is_active' => true,
            ]
        );

        FeedType::updateOrCreate(
            ['code' => 'PG-STARTER'],
            [
                'species_id' => $pigId,
                'name' => 'Pig Starter',
                'category' => 'starter',
                'protein_percentage' => 20,
                'energy_mj_kg' => 14.0,
                'is_active' => true,
            ]
        );

        FeedType::updateOrCreate(
            ['code' => 'PG-GROWER'],
            [
                'species_id' => $pigId,
                'name' => 'Pig Grower',
                'category' => 'grower',
                'protein_percentage' => 18,
                'energy_mj_kg' => 13.5,
                'is_active' => true,
            ]
        );

        $this->command->info('Feed types seeded successfully!');
    }
}