<?php
// database/seeders/DailyLogSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyLog;
use App\Models\Flock;
use Carbon\Carbon;

class DailyLogSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();
        
        if ($flock) {
            // Create last 7 days of logs
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $ageInDays = 25 - $i;
                
                // Determine if it's a laying period (assuming chickens start laying around 18-20 weeks)
                $isLaying = $ageInDays >= 18; // Age in weeks? Adjust based on your actual age calculation
                $eggsCollected = $isLaying ? rand(80, 120) : 0;
                $eggsDamaged = $isLaying ? rand(0, round($eggsCollected * 0.05)) : 0;
                
                DailyLog::updateOrCreate(
                    [
                        'flock_id' => $flock->id,
                        'log_date' => $date,
                    ],
                    [
                        'mortality_count' => rand(2, 8),
                        'culling_count' => rand(0, 3),
                        'eggs_collected' => $eggsCollected,
                        'eggs_damaged' => $eggsDamaged,
                        'feed_intake_kg' => rand(450, 550),
                        'water_consumption_liters' => rand(900, 1100),
                        'average_weight_kg' => round(0.5 + ($ageInDays * 0.08), 2),
                        'min_temperature_c' => rand(28, 30),
                        'max_temperature_c' => rand(31, 34),
                        'min_humidity' => rand(55, 65),
                        'max_humidity' => rand(66, 75),
                        'ammonia_ppm' => rand(5, 15),
                        'notes' => 'Routine check - all birds healthy',
                        'created_by' => 1,
                    ]
                );
            }
        }

        $this->command->info('Daily logs seeded successfully!');
    }
}