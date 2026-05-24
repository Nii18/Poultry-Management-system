<?php
// database/seeders/SettingsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'farm_name', 'value' => 'Green Acres Livestock Farm'],
            ['key' => 'farm_address', 'value' => '123 Farm Road, Rural District, Ghana'],
            ['key' => 'farm_phone', 'value' => '+233 20 123 4567'],
            ['key' => 'farm_email', 'value' => 'contact@greenacresfarm.com'],
            ['key' => 'timezone', 'value' => 'Africa/Accra'],
            ['key' => 'date_format', 'value' => 'Y-m-d'],
            ['key' => 'currency', 'value' => 'GHS'],
            ['key' => 'mortality_threshold', 'value' => '3'],
            ['key' => 'temperature_deviation', 'value' => '3'],
            ['key' => 'ammonia_threshold', 'value' => '25'],
            ['key' => 'low_feed_threshold_kg', 'value' => '500'],
            ['key' => 'withdrawal_alert_days', 'value' => '3'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'updated_at' => now()]
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}