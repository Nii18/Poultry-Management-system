<?php
// database/seeders/DailyLogSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyLog;
use App\Models\FarmProduce;
use App\Models\Flock;
use Carbon\Carbon;

class DailyLogSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();

        if (!$flock) {
            $this->command->warn('Flock not found. Skipping daily log seeding.');
            return;
        }

        for ($i = 29; $i >= 0; $i--) {   // last 30 days for more data
            $date          = Carbon::now()->subDays($i)->toDateString();
            $eggsCollected = rand(80, 120);
            $eggsDamaged   = rand(0, (int) round($eggsCollected * 0.05));

            // 1. Create / update the daily log
            $log = DailyLog::updateOrCreate(
                [
                    'flock_id' => $flock->id,
                    'log_date' => $date,
                ],
                [
                    'mortality_count'          => rand(0, 5),
                    'culling_count'            => rand(0, 2),
                    'eggs_collected'           => $eggsCollected,
                    'eggs_damaged'             => $eggsDamaged,
                    'feed_intake_kg'           => rand(450, 550),
                    'water_consumption_liters' => rand(900, 1100),
                    'average_weight_kg'        => round(1.8 + (rand(0, 30) * 0.01), 2),
                    'min_temperature_c'        => rand(28, 30),
                    'max_temperature_c'        => rand(31, 34),
                    'min_humidity'             => rand(55, 65),
                    'max_humidity'             => rand(66, 75),
                    'ammonia_ppm'              => rand(5, 15),
                    'notes'                    => 'Routine check - all birds healthy',
                    'created_by'               => 1,
                ]
            );

            // 2. Mirror the egg produce record (same logic as the controller)
            FarmProduce::updateOrCreate(
                [
                    'flock_id'     => $flock->id,
                    'product_type' => 'eggs',
                    'produce_date' => $date,
                ],
                [
                    'quantity'         => $eggsCollected,
                    'quantity_damaged' => $eggsDamaged,
                    'unit'             => 'pieces',
                    'notes'            => "Auto-recorded from daily log. Damaged: {$eggsDamaged}",
                    'created_by'       => 1,
                ]
            );
        }

        $this->command->info('Daily logs + egg produce records seeded successfully!');
    }
}