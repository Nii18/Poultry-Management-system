<?php
// database/seeders/OffspringRecordSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OffspringRecord;
use App\Models\BreedingRecord;
use App\Models\Flock;

class OffspringRecordSeeder extends Seeder
{
    public function run()
    {
        $breedingRecord = BreedingRecord::where('is_successful', true)->first();
        
        if ($breedingRecord) {
            OffspringRecord::create([
                'breeding_record_id' => $breedingRecord->id,
                'new_flock_id' => null, // Not yet assigned to a new flock
                'count' => $breedingRecord->offspring_count ?? 10,
                'average_birth_weight_kg' => 1.2,
                'ear_tag_prefix' => 'PIG-',
                'ear_tag_start_number' => 100,
                'notes' => 'Healthy offspring, ready for weaning',
            ]);
        }

        $this->command->info('Offspring records seeded successfully!');
    }
}