<?php
// database/seeders/VaccinationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vaccination;
use App\Models\Flock;
use Carbon\Carbon;

class VaccinationSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();

        // Day 1 - Marek's Vaccine
        Vaccination::create([
            'flock_id' => $flock->id,
            'vaccine_name' => 'Marek\'s Vaccine',
            'disease_target' => 'Marek\'s Disease',
            'day_administered' => 1,
            'administration_date' => $flock->start_date,
            'route' => 'subcutaneous',
            'batch_number' => 'MRK-2024-001',
            'expiry_date' => Carbon::now()->addMonths(6),
            'dosage_ml' => 0.2,
            'birds_vaccinated' => $flock->initial_count,
            'notes' => 'Administered at hatchery',
            'administered_by' => 1,
        ]);

        // Day 10 - Gumboro Vaccine
        Vaccination::create([
            'flock_id' => $flock->id,
            'vaccine_name' => 'Gumboro Vaccine',
            'disease_target' => 'Infectious Bursal Disease',
            'day_administered' => 10,
            'administration_date' => $flock->start_date->addDays(10),
            'route' => 'drinking_water',
            'batch_number' => 'GMB-2024-002',
            'expiry_date' => Carbon::now()->addMonths(8),
            'dosage_ml' => null,
            'birds_vaccinated' => $flock->current_count,
            'notes' => 'Administered via drinking water',
            'administered_by' => 1,
        ]);

        // Day 18 - Newcastle Disease Vaccine
        Vaccination::create([
            'flock_id' => $flock->id,
            'vaccine_name' => 'Newcastle Disease Vaccine',
            'disease_target' => 'Newcastle Disease',
            'day_administered' => 18,
            'administration_date' => $flock->start_date->addDays(18),
            'route' => 'eye_drop',
            'batch_number' => 'NCD-2024-003',
            'expiry_date' => Carbon::now()->addMonths(12),
            'dosage_ml' => 0.05,
            'birds_vaccinated' => $flock->current_count,
            'notes' => 'Eye drop administration',
            'administered_by' => 1,
        ]);

        $this->command->info('Vaccinations seeded successfully!');
    }
}