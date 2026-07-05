<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\{
    User,
    Species,
    House,
    Flock,
    FlockBreederLog,
    FeedType,
    FeedDelivery,
    FeedIssuance,
    DailyLog,
    FarmProduce,
    Vaccination,
    Treatment,
    HealthRecord,
    BreedingRecord,
    OffspringRecord,
    Expense,
    PerformanceMetric,
    Sale,
    WorkerTask,
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding with factories...');
        
        // ─── 1. Core Data ────────────────────────────────────────────────────
        $this->command->info('📦 Seeding core data...');
        
        // Users
        $users = User::factory(10)->create();
        $admin = User::factory()->admin()->create(['email' => 'admin@livestock.com']);
        $manager = User::factory()->manager()->create(['email' => 'manager@livestock.com']);
        $workers = User::factory(5)->worker()->create();
        
        // Add specific worker scenarios for activity testing
        $this->command->info('👷 Creating worker test scenarios...');
        $this->createWorkerTestScenarios();
        
        // Species - Use the existing seeder for consistency with default data
        $this->command->info('🐾 Seeding species...');
        $this->call(SpeciesSeeder::class);
        $species = Species::all();
    
        
        // ─── 2. Farm Infrastructure ──────────────────────────────────────────
        $this->command->info('🏠 Building farm infrastructure...');
        
        // Houses
        $houses = House::factory(8)->active()->create();
        
        // Add some houses in maintenance
        House::factory(2)->state(['status' => 'maintenance'])->create();
        
        // ─── 3. Flocks ──────────────────────────────────────────────────────
        $this->command->info('🐔 Creating flocks...');
        
        // Create regular active flocks
        $flocks = Flock::factory(15)
            ->active()
            ->create();
        
        // Create some meat production flocks
        Flock::factory(5)
            ->active()
            ->meat()
            ->create();
        
        // Create some egg production flocks
        Flock::factory(3)
            ->active()
            ->eggs()
            ->create();
        
        // Create closed flocks (sold/completed)
        $closedFlocks = Flock::factory(5)
            ->closed()
            ->create();
        
        // Create planned flocks (future)
        Flock::factory(3)
            ->planned()
            ->create();
        
        // Create breeding flocks with their logs
        $femaleBreeding = Flock::factory(3)
            ->femaleBreeding()
            ->withBreederLogs()
            ->create();
        
        $maleBreeding = Flock::factory(2)
            ->maleBreeding()
            ->withBreederLogs()
            ->create();
        
        // Create a specific breeding setup with exact counts
        Flock::factory()
            ->breedingWithCounts(20, 5)
            ->create();
        
        // ─── 4. Daily Operations ────────────────────────────────────────────
        $this->command->info('📊 Recording daily operations...');
        
        // Feed Types
        $feedTypes = FeedType::factory(10)->create();
        
        // Feed Deliveries
        $deliveries = FeedDelivery::factory(20)->create();
        
        // Add some low stock alerts
        FeedDelivery::factory(3)->lowStock()->create();
        
        // Add some expiring soon deliveries
        FeedDelivery::factory(2)->expiringSoon()->create();
        
        // Feed Issuances (for active flocks)
        foreach ($flocks->take(5) as $flock) {
            FeedIssuance::factory(10)
                ->forFlock($flock)
                ->create();
        }
        
        // Daily Logs with produce
        foreach ($flocks->take(5) as $flock) {
            for ($i = 0; $i < 30; $i++) {
                DailyLog::factory()
                    ->forFlock($flock)
                    ->withProduce()
                    ->create([
                        'log_date' => Carbon::now()->subDays($i)->toDateString(),
                        'created_by' => $admin->id, // add this
                    ]);
            }
        }
        
        // ─── 5. Health Management ───────────────────────────────────────────
        $this->command->info('💉 Managing health records...');
        
        // Vaccinations
        foreach ($flocks as $flock) {
            Vaccination::factory(3)->create(['flock_id' => $flock->id]);
        }
        
        // Vaccinations for closed flocks (historical)
        foreach ($closedFlocks as $flock) {
            Vaccination::factory(2)->create(['flock_id' => $flock->id]);
        }
        
        // Treatments
        foreach ($flocks->take(8) as $flock) {
            Treatment::factory(2)->create(['flock_id' => $flock->id]);
        }
        
        // Active treatments (still in withdrawal period)
        Treatment::factory(3)->active()->create();
        
        // Health Records
        foreach ($flocks->take(10) as $flock) {
            HealthRecord::factory(5)->create(['flock_id' => $flock->id]);
        }
        
        // Routine checkups
        HealthRecord::factory(5)->checkup()->create();
        
        // Critical health events
        HealthRecord::factory(2)
            ->critical()
            ->create();
        
        // ─── 6. Breeding ────────────────────────────────────────────────────
        $this->command->info('🧬 Recording breeding activities...');
        
        if ($femaleBreeding->isNotEmpty() && $maleBreeding->isNotEmpty()) {
            // Natural breeding records
            $breedingRecords = BreedingRecord::factory(10)
                ->successful()
                ->create([
                    'flock_id' => $femaleBreeding->random()->id,
                    'mate_id' => $maleBreeding->random()->id,
                ]);
            
            // AI breeding records
            BreedingRecord::factory(5)
                ->artificial()
                ->pending()
                ->create([
                    'flock_id' => $femaleBreeding->random()->id,
                ]);
            
            // Some unsuccessful breeding attempts
            BreedingRecord::factory(3)
                ->create([
                    'flock_id' => $femaleBreeding->random()->id,
                    'mate_id' => $maleBreeding->random()->id,
                    'is_successful' => false,
                ]);
            
            // Offspring records
            foreach ($breedingRecords->take(5) as $record) {
                OffspringRecord::factory()->create([
                    'breeding_record_id' => $record->id,
                ]);
            }
            
            // Offspring records with new flock assignment
            foreach ($breedingRecords->take(3) as $record) {
                OffspringRecord::factory()->create([
                    'breeding_record_id' => $record->id,
                    'new_flock_id' => Flock::factory()->active()->create()->id,
                ]);
            }
        }
        
        // ─── 7. Financial ────────────────────────────────────────────────────
        $this->command->info('💰 Recording financial data...');
        
        // Expenses
        Expense::factory(30)->create();
        Expense::factory(10)->feed()->create();
        Expense::factory(5)->labor()->create();
        
        // Large expenses
        Expense::factory(3)->create([
            'amount' => fake()->numberBetween(10000, 50000),
        ]);
        
        // Sales (12 months of data)
        $this->command->info('📈 Generating 12 months of sales data...');
        Sale::factory(200)->create();
        
        // Specific product sales
        Sale::factory(20)->eggs()->create();
        Sale::factory(15)->liveBird()->create();
        Sale::factory(10)->meat()->create();
        
        // Performance Metrics
        PerformanceMetric::factory(10)->forClosedFlock()->create();
        PerformanceMetric::factory(15)->forActiveFlock()->create();
        
        // ─── 8. Worker Tasks ────────────────────────────────────────────────
        $this->command->info('📋 Creating worker tasks...');
        
        WorkerTask::factory(20)->pending()->create();
        WorkerTask::factory(15)->completed()->create();
        WorkerTask::factory(5)->overdue()->create();
        WorkerTask::factory(10)->recurring()->create();
        
        // Assign tasks to specific workers
        foreach ($workers as $worker) {
            WorkerTask::factory(3)->create([
                'assigned_to' => $worker->id,
            ]);
        }
        
        
        // ─── 10. Cleanup and Verification ──────────────────────────────────
        $this->command->info('🧹 Verifying data integrity...');
        
        // Ensure all breeding flocks have breeder logs
        $breedingFlocks = Flock::where('is_breeding_stock', true)->get();
        foreach ($breedingFlocks as $flock) {
            if ($flock->breederLogs()->count() === 0) {
                FlockBreederLog::factory()
                    ->initialCount()
                    ->create([
                        'flock_id' => $flock->id,
                        'breeder_count' => $flock->initial_count ?? 10,
                    ]);
                $this->command->warn("⚠️ Added missing breeder log for flock: {$flock->flock_number}");
            }
        }
        
        // ─── 11. Summary ────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('🎉 Seeding completed successfully!');
        $this->command->newLine();
        
        $this->showSummary();
    }
    
    private function createWorkerTestScenarios(): void
    {
        // Online now
        User::factory()->worker()->online()->create([
            'name' => 'John Mensah',
            'email' => 'john.mensah@livestock.com',
        ]);
        
        // Active recently
        User::factory()->worker()->create([
            'name' => 'Ama Owusu',
            'email' => 'ama.owusu@livestock.com',
            'last_seen_at' => now()->subMinutes(20),
            'last_login_at' => now()->subMinutes(20),
            'last_activity_at' => now()->subMinutes(20),
        ]);
        
        // Offline but active today
        User::factory()->worker()->create([
            'name' => 'Kofi Asante',
            'email' => 'kofi.asante@livestock.com',
            'last_seen_at' => now()->subHours(5),
            'last_login_at' => now()->subHours(5),
            'last_activity_at' => now()->subHours(5),
        ]);
        
        // Inactive 3 days
        User::factory()->worker()->create([
            'name' => 'Abena Boateng',
            'email' => 'abena.boateng@livestock.com',
            'last_seen_at' => now()->subDays(3),
            'last_login_at' => now()->subDays(3),
            'last_activity_at' => now()->subDays(3),
        ]);
        
        // Inactive 10 days
        User::factory()->worker()->create([
            'name' => 'Kwame Darko',
            'email' => 'kwame.darko@livestock.com',
            'last_seen_at' => now()->subDays(10),
            'last_login_at' => now()->subDays(10),
            'last_activity_at' => now()->subDays(10),
        ]);
        
        // Inactive 20 days
        User::factory()->worker()->create([
            'name' => 'Efua Appiah',
            'email' => 'efua.appiah@livestock.com',
            'last_seen_at' => now()->subDays(20),
            'last_login_at' => now()->subDays(20),
            'last_activity_at' => now()->subDays(20),
        ]);
        
        // Never logged in
        User::factory()->worker()->neverLoggedIn()->create([
            'name' => 'Yaw Frimpong',
            'email' => 'yaw.frimpong@livestock.com',
        ]);
    }
    
    private function showSummary(): void
    {
        $this->command->info('📊 Seeding Summary:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $models = [
            'Users' => User::count(),
            'Species' => Species::count(),
            'Houses' => House::count(),
            'Flocks' => Flock::count(),
            'Breeder Logs' => FlockBreederLog::count(),
            'Feed Types' => FeedType::count(),
            'Feed Deliveries' => FeedDelivery::count(),
            'Feed Issuances' => FeedIssuance::count(),
            'Daily Logs' => DailyLog::count(),
            'Farm Produce' => FarmProduce::count(),
            'Vaccinations' => Vaccination::count(),
            'Treatments' => Treatment::count(),
            'Health Records' => HealthRecord::count(),
            'Breeding Records' => BreedingRecord::count(),
            'Offspring Records' => OffspringRecord::count(),
            'Expenses' => Expense::count(),
            'Sales' => Sale::count(),
            'Performance Metrics' => PerformanceMetric::count(),
            'Worker Tasks' => WorkerTask::count(),
        ];
        
        foreach ($models as $name => $count) {
            $this->command->info(sprintf('  %-20s %5d', $name . ':', $count));
        }
        
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Additional statistics
        $activeFlocks = Flock::where('status', 'active')->count();
        $breedingFlocks = Flock::where('is_breeding_stock', true)->count();
        $totalRevenue = Sale::sum('total_amount');
        
        $this->command->info('📈 Additional Stats:');
        $this->command->info(sprintf('  %-20s %5d', 'Active Flocks:', $activeFlocks));
        $this->command->info(sprintf('  %-20s %5d', 'Breeding Flocks:', $breedingFlocks));
        $this->command->info(sprintf('  %-20s %5s', 'Total Revenue:', 'GHS ' . number_format($totalRevenue, 2)));
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ All seeders completed successfully!');
    }
}