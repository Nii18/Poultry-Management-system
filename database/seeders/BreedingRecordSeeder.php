<?php
// database/seeders/BreedingRecordSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreedingRecord;
use App\Models\Flock;
use Carbon\Carbon;

class BreedingRecordSeeder extends Seeder
{
    public function run()
    {
        // Get pig flock for breeding (assuming pigs are breeding stock)
        $pigFlock = Flock::where('species_id', 2)->first(); // Species ID 2 is Pig
        
        if (!$pigFlock) {
            $this->command->warn('No pig flock found. Skipping breeding records.');
            return;
        }

        try {
            // Successful Breeding Record
            BreedingRecord::create([
                'flock_id' => $pigFlock->id,
                'mate_id' => null, // External AI
                'breeding_date' => Carbon::now()->subDays(100),
                'expected_delivery_date' => Carbon::now()->subDays(14),
                'actual_delivery_date' => Carbon::now()->subDays(14),
                'breeding_method' => 'artificial_insemination',
                'is_successful' => true,
                'offspring_count' => 12,
                'stillborn_count' => 1,
                'weaned_count' => 10,
                'notes' => 'Healthy litter, 12 piglets born',
                'recorded_by' => 1,
            ]);

            // Pending Breeding (Expected delivery in future)
            BreedingRecord::create([
                'flock_id' => $pigFlock->id,
                'mate_id' => null,
                'breeding_date' => Carbon::now()->subDays(60),
                'expected_delivery_date' => Carbon::now()->addDays(54),
                'actual_delivery_date' => null,
                'breeding_method' => 'natural',
                'is_successful' => false,
                'offspring_count' => 0,
                'stillborn_count' => 0,
                'weaned_count' => 0,
                'notes' => 'Expecting delivery in 54 days',
                'recorded_by' => 1,
            ]);

            $this->command->info('Breeding records seeded successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('Error seeding breeding records: ' . $e->getMessage());
        }
    }
}