<?php
// database/seeders/TreatmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Treatment;
use App\Models\Flock;
use Carbon\Carbon;

class TreatmentSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();

        // Active Treatment (In Withdrawal Period)
        Treatment::create([
            'flock_id' => $flock->id,
            'diagnosis' => 'Coccidiosis',
            'product_name' => 'Amprolium',
            'active_ingredient' => 'Amprolium Hydrochloride',
            'dosage' => '2ml per liter of water',
            'administration_route' => 'water',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(3),
            'withdrawal_days' => 7,
            'withdrawal_end_date' => Carbon::now()->addDays(4),
            'batch_number' => 'AMP-2024-001',
            'animals_treated' => 500,
            'cost' => 250.00,
            'notes' => 'Treatment for coccidiosis outbreak',
            'prescribed_by' => 1,
        ]);

        // Past Treatment (Completed)
        Treatment::create([
            'flock_id' => $flock->id,
            'diagnosis' => 'Respiratory Infection',
            'product_name' => 'Tylosin',
            'active_ingredient' => 'Tylosin Tartrate',
            'dosage' => '1g per liter of water',
            'administration_route' => 'water',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(15),
            'withdrawal_days' => 5,
            'withdrawal_end_date' => Carbon::now()->subDays(10),
            'batch_number' => 'TYL-2024-002',
            'animals_treated' => 400,
            'cost' => 180.00,
            'notes' => 'Treatment for respiratory issues',
            'prescribed_by' => 1,
        ]);

        $this->command->info('Treatments seeded successfully!');
    }
}