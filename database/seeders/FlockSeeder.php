<?php
// database/seeders/FlockSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flock;
use App\Models\Species;
use App\Models\House;
use Carbon\Carbon;

class FlockSeeder extends Seeder
{
    public function run()
    {
        $chickenId = Species::where('code', 'CH')->first()->id;
        $pigId = Species::where('code', 'PG')->first()->id;
        
        $house1 = House::where('house_code', 'H01')->first()->id;
        $house2 = House::where('house_code', 'H02')->first()->id;
        $house3 = House::where('house_code', 'H03')->first()->id;

        // Active Broiler Flock
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H01-001'],
            [
                'species_id' => $chickenId,
                'house_id' => $house1,
                'breed_variety' => 'Cobb 500',
                'start_date' => Carbon::now()->subDays(25),
                'initial_count' => 5000,
                'current_count' => 4850,
                'source' => 'Local Hatchery',
                'production_type' => 'meat',
                'status' => 'active',
                'created_by' => 1,
            ]
        );

        // Active Layer Flock
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H02-001'],
            [
                'species_id' => $chickenId,
                'house_id' => $house2,
                'breed_variety' => 'ISA Brown',
                'start_date' => Carbon::now()->subDays(120),
                'initial_count' => 3000,
                'current_count' => 2850,
                'source' => 'Breeder Farm',
                'production_type' => 'eggs',
                'status' => 'active',
                'created_by' => 1,
            ]
        );

        // Active Pig Flock
        Flock::updateOrCreate(
            ['flock_number' => '2024-PG-H03-001'],
            [
                'species_id' => $pigId,
                'house_id' => $house3,
                'breed_variety' => 'Large White',
                'start_date' => Carbon::now()->subDays(60),
                'initial_count' => 200,
                'current_count' => 195,
                'source' => 'Breeding Farm',
                'production_type' => 'meat',
                'status' => 'active',
                'created_by' => 1,
            ]
        );

        // Closed Flock (Example)
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H01-000'],
            [
                'species_id' => $chickenId,
                'house_id' => $house1,
                'breed_variety' => 'Ross 308',
                'start_date' => Carbon::now()->subDays(70),
                'end_date' => Carbon::now()->subDays(28),
                'initial_count' => 4800,
                'final_count' => 4650,
                'current_count' => 0,
                'source' => 'Hatchery',
                'production_type' => 'meat',
                'status' => 'closed',
                'total_weight_kg' => 11160,
                'average_price_per_kg' => 2.5,
                'total_revenue' => 27900,
                'created_by' => 1,
            ]
        );

        $this->command->info('Flocks seeded successfully!');
    }
}