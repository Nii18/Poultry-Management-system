<?php
// database/seeders/HouseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\House;
use App\Models\Species;

class HouseSeeder extends Seeder
{
    public function run()
    {
        $chickenId = Species::where('code', 'CH')->first()->id;
        $pigId = Species::where('code', 'PG')->first()->id;
        $cattleId = Species::where('code', 'CT')->first()->id;

        House::updateOrCreate(
            ['house_code' => 'H01'],
            [
                'name' => 'Broiler House A',
                'species_id' => $chickenId,
                'capacity' => 5000,
                'length_m' => 50,
                'width_m' => 20,
                'height_m' => 3.5,
                'feeders_count' => 25,
                'drinkers_count' => 40,
                'fans_count' => 10,
                'heaters_count' => 5,
                'status' => 'active',
                'notes' => 'Main broiler house for meat production'
            ]
        );

        House::updateOrCreate(
            ['house_code' => 'H02'],
            [
                'name' => 'Layer House B',
                'species_id' => $chickenId,
                'capacity' => 3000,
                'length_m' => 40,
                'width_m' => 15,
                'height_m' => 3,
                'feeders_count' => 20,
                'drinkers_count' => 30,
                'fans_count' => 8,
                'heaters_count' => 3,
                'status' => 'active',
                'notes' => 'Egg production house'
            ]
        );

        House::updateOrCreate(
            ['house_code' => 'H03'],
            [
                'name' => 'Pig Barn',
                'species_id' => $pigId,
                'capacity' => 200,
                'length_m' => 60,
                'width_m' => 25,
                'height_m' => 4,
                'feeders_count' => 30,
                'drinkers_count' => 40,
                'fans_count' => 12,
                'heaters_count' => 6,
                'status' => 'active',
                'notes' => 'Pig rearing facility'
            ]
        );

        House::updateOrCreate(
            ['house_code' => 'H04'],
            [
                'name' => 'Cattle Shed',
                'species_id' => $cattleId,
                'capacity' => 50,
                'length_m' => 80,
                'width_m' => 30,
                'height_m' => 5,
                'feeders_count' => 20,
                'drinkers_count' => 25,
                'fans_count' => 8,
                'heaters_count' => 2,
                'status' => 'maintenance',
                'notes' => 'Currently under maintenance'
            ]
        );

        $this->command->info('Houses seeded successfully!');
    }
}