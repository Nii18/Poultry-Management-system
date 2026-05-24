<?php
// database/seeders/NotificationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Flock;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();

        // Critical Alert - High Mortality
        Notification::create([
            'user_id' => 1,
            'flock_id' => $flock->id,
            'type' => 'high_mortality',
            'title' => 'High Mortality Detected',
            'message' => 'Mortality rate of 4.2% detected in flock ' . $flock->flock_number,
            'severity' => 'critical',
            'data' => json_encode([
                'mortality_rate' => 4.2,
                'flock_id' => $flock->id
            ]),
            'read_at' => null,
            'sent_at' => Carbon::now()->subHours(2),
        ]);

        // Warning Alert - Low Feed Stock
        Notification::create([
            'user_id' => 1,
            'flock_id' => null,
            'type' => 'low_feed',
            'title' => 'Low Feed Stock Alert',
            'message' => 'Feed stock is below 500kg. Please reorder soon.',
            'severity' => 'warning',
            'data' => json_encode([
                'current_stock' => 450,
                'threshold' => 500
            ]),
            'read_at' => null,
            'sent_at' => Carbon::now()->subDay(),
        ]);

        // Info Alert - Upcoming Vaccination
        Notification::create([
            'user_id' => 1,
            'flock_id' => $flock->id,
            'type' => 'vaccination_reminder',
            'title' => 'Vaccination Due Soon',
            'message' => 'Booster vaccination is due in 3 days.',
            'severity' => 'info',
            'data' => json_encode([
                'vaccine' => 'Newcastle',
                'due_date' => Carbon::now()->addDays(3)->format('Y-m-d')
            ]),
            'read_at' => Carbon::now()->subHours(5),
            'sent_at' => Carbon::now()->subDay(),
        ]);

        // Read Notification
        Notification::create([
            'user_id' => 1,
            'flock_id' => null,
            'type' => 'system_update',
            'title' => 'System Update Completed',
            'message' => 'The system has been successfully updated to version 2.0.',
            'severity' => 'info',
            'data' => json_encode(['version' => '2.0']),
            'read_at' => Carbon::now()->subHours(48),
            'sent_at' => Carbon::now()->subDays(2),
        ]);

        $this->command->info('Notifications seeded successfully!');
    }
}