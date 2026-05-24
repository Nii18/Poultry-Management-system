<?php
// database/seeders/HealthRecordSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthRecord;
use App\Models\Flock;
use Carbon\Carbon;

class HealthRecordSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();
        
        if (!$flock) {
            $this->command->warn('No flock found. Skipping health records.');
            return;
        }

        try {
            // Routine Checkup
            HealthRecord::create([
                'flock_id' => $flock->id,
                'record_type' => 'checkup',
                'condition' => 'Routine Health Check',
                'symptoms' => json_encode(['normal' => true, 'active' => true]),
                'lab_results' => null,
                'veterinarian_notes' => 'All birds appear healthy. No signs of disease.',
                'affected_count' => 0,
                'severity' => 'info',
                'record_date' => Carbon::now()->subDays(10),
                'recorded_by' => 1,
            ]);

            // Symptom Observation
            HealthRecord::create([
                'flock_id' => $flock->id,
                'record_type' => 'symptom',
                'condition' => 'Possible Respiratory Issue',
                'symptoms' => json_encode([
                    'coughing' => true,
                    'sneezing' => true,
                    'nasal_discharge' => false,
                    'lethargy' => false
                ]),
                'lab_results' => null,
                'veterinarian_notes' => 'Observed mild coughing in some birds. Monitor closely.',
                'affected_count' => 50,
                'severity' => 'warning',
                'record_date' => Carbon::now()->subDays(7),
                'recorded_by' => 1,
            ]);

            $this->command->info('Health records seeded successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('Error seeding health records: ' . $e->getMessage());
        }
    }
}