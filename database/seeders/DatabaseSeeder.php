<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core Data
            UserSeeder::class,
            SpeciesSeeder::class,
            SettingsSeeder::class,
            
            // Farm Infrastructure
            HouseSeeder::class,
            FlockSeeder::class,
            
            // Daily Operations
            DailyLogSeeder::class,
            FeedTypeSeeder::class,
            FeedDeliverySeeder::class,
            FeedIssuanceSeeder::class,
            
            // Health Management
            VaccinationSeeder::class,
            TreatmentSeeder::class,
            HealthRecordSeeder::class,
            
            // Breeding
            BreedingRecordSeeder::class,
            OffspringRecordSeeder::class,
            
            // Financial
            ExpenseSeeder::class,
            PerformanceMetricSeeder::class,
            
            // Notifications
            NotificationSeeder::class,
        ]);
        
        $this->command->info('========================================');
        $this->command->info('🎉 ALL SEEDERS COMPLETED SUCCESSFULLY! 🎉');
        $this->command->info('========================================');
        $this->command->info('Total Records Seeded:');
        $this->command->info('  - Users: 5');
        $this->command->info('  - Species: 5');
        $this->command->info('  - Houses: 4');
        $this->command->info('  - Flocks: 4');
        $this->command->info('  - Daily Logs: 7');
        $this->command->info('  - Feed Types: 5');
        $this->command->info('  - Feed Deliveries: 4');
        $this->command->info('  - Feed Issuances: 10');
        $this->command->info('  - Vaccinations: 3');
        $this->command->info('  - Treatments: 2');
        $this->command->info('  - Health Records: 2');
        $this->command->info('  - Breeding Records: 2');
        $this->command->info('  - Offspring Records: 1');
        $this->command->info('  - Expenses: 7');
        $this->command->info('  - Performance Metrics: 2');
        $this->command->info('  - Notifications: 4');
        $this->command->info('  - Settings: 12');
        $this->command->info('========================================');
    }
}