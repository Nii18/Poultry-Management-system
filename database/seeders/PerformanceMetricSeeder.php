<?php
// database/seeders/PerformanceMetricSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformanceMetric;
use App\Models\Flock;
use Carbon\Carbon;

class PerformanceMetricSeeder extends Seeder
{
    public function run()
    {
        $closedFlock = Flock::where('status', 'closed')->first();
        
        if ($closedFlock) {
            PerformanceMetric::create([
                'flock_id' => $closedFlock->id,
                'mortality_rate' => 3.1,
                'feed_conversion_ratio' => 1.68,
                'average_daily_gain_kg' => 0.058,
                'total_feed_consumed_kg' => 7560,
                'total_weight_gained_kg' => 4500,
                'total_revenue' => 27900,
                'total_cost' => 15840,
                'net_profit' => 12060,
                'roi_percentage' => 76.14,
                'calculated_date' => Carbon::now()->subDays(28),
            ]);
        }

        $activeFlock = Flock::where('flock_number', '2024-CH-H01-001')->first();
        
        if ($activeFlock) {
            PerformanceMetric::create([
                'flock_id' => $activeFlock->id,
                'mortality_rate' => 3.0,
                'feed_conversion_ratio' => 1.55,
                'average_daily_gain_kg' => 0.062,
                'total_feed_consumed_kg' => 4850,
                'total_weight_gained_kg' => 3129,
                'total_revenue' => null,
                'total_cost' => 8250,
                'net_profit' => null,
                'roi_percentage' => null,
                'calculated_date' => Carbon::now(),
            ]);
        }

        $this->command->info('Performance metrics seeded successfully!');
    }
}