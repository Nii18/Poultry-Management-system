<?php
// database/seeders/FeedIssuanceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedIssuance;
use App\Models\Flock;
use App\Models\FeedDelivery;
use Carbon\Carbon;

class FeedIssuanceSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();
        $feedDelivery = FeedDelivery::where('invoice_number', 'INV-2024-002')->first();

        // Create feed issuances for the last 10 days
        for ($i = 9; $i >= 0; $i--) {
            FeedIssuance::create([
                'flock_id' => $flock->id,
                'feed_delivery_id' => $feedDelivery->id,
                'quantity_kg' => rand(450, 550),
                'issuance_date' => Carbon::now()->subDays($i),
                'issuance_time' => '08:00:00',
                'notes' => 'Daily feeding',
                'issued_by' => 1,
            ]);
        }

        $this->command->info('Feed issuances seeded successfully!');
    }
}