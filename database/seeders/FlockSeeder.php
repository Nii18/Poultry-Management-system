<?php
// database/seeders/FlockSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flock;
use App\Models\FlockBreederLog;
use App\Models\Species;
use App\Models\House;
use Carbon\Carbon;

class FlockSeeder extends Seeder
{
    public function run()
    {
        $chickenId = Species::where('code', 'CH')->first()->id;
        $pigId     = Species::where('code', 'PG')->first()->id;

        $house1 = House::where('house_code', 'H01')->first()->id;
        $house2 = House::where('house_code', 'H02')->first()->id;
        $house3 = House::where('house_code', 'H03')->first()->id;

        // ─────────────────────────────────────────────────────────────────
        // Active Broiler Flock (meat, mixed-sex, NOT breeding stock)
        // ─────────────────────────────────────────────────────────────────
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H01-001'],
            [
                'species_id'        => $chickenId,
                'house_id'          => $house1,
                'breed_variety'     => 'Cobb 500',
                'start_date'        => Carbon::now()->subDays(25),
                'initial_count'     => 5000,
                // 'current_count' intentionally omitted — it's a computed
                // accessor (initial_count - total_mortality from daily_logs),
                // not a stored column. Writing it here would be a dead write.
                'source'            => 'Local Hatchery',
                'production_type'   => 'meat',
                'is_breeding_stock' => false,
                'sex'               => null,
                'status'            => 'active',
                'created_by'        => 1,
            ]
        );

        // ─────────────────────────────────────────────────────────────────
        // Active Layer Flock (eggs, mixed-sex, NOT breeding stock)
        // ─────────────────────────────────────────────────────────────────
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H02-001'],
            [
                'species_id'        => $chickenId,
                'house_id'          => $house2,
                'breed_variety'     => 'ISA Brown',
                'start_date'        => Carbon::now()->subDays(120),
                'initial_count'     => 3000,
                'source'            => 'Breeder Farm',
                'production_type'   => 'eggs',
                'is_breeding_stock' => false,
                'sex'               => null,
                'status'            => 'active',
                'created_by'        => 1,
            ]
        );

        // ─────────────────────────────────────────────────────────────────
        // Active Pig Flock (meat, mixed-sex, NOT breeding stock)
        // ─────────────────────────────────────────────────────────────────
        Flock::updateOrCreate(
            ['flock_number' => '2024-PG-H03-001'],
            [
                'species_id'        => $pigId,
                'house_id'          => $house3,
                'breed_variety'     => 'Large White',
                'start_date'        => Carbon::now()->subDays(60),
                'initial_count'     => 200,
                'source'            => 'Breeding Farm',
                'production_type'   => 'meat',
                'is_breeding_stock' => false,
                'sex'               => null,
                'status'            => 'active',
                'created_by'        => 1,
            ]
        );

        // ─────────────────────────────────────────────────────────────────
        // Closed Flock (historical — already sold off)
        // ─────────────────────────────────────────────────────────────────
        Flock::updateOrCreate(
            ['flock_number' => '2024-CH-H01-000'],
            [
                'species_id'           => $chickenId,
                'house_id'             => $house1,
                'breed_variety'        => 'Ross 308',
                'start_date'           => Carbon::now()->subDays(70),
                'end_date'             => Carbon::now()->subDays(28),
                'initial_count'        => 4800,
                'final_count'          => 4650,
                'source'               => 'Hatchery',
                'production_type'      => 'meat',
                'is_breeding_stock'    => false,
                'sex'                  => null,
                'status'               => 'closed',
                'total_weight_kg'      => 11160,
                'average_price_per_kg' => 2.5,
                'total_revenue'        => 27900,
                'created_by'           => 1,
            ]
        );

        // ═══════════════════════════════════════════════════════════════════
        // BREEDING STOCK — required for BreedingRecordController, which
        // filters dropdowns by is_breeding_stock = true AND sex = male/female.
        // Without these, every breeding dropdown in the app is empty.
        // ═══════════════════════════════════════════════════════════════════

        // Dam (female) breeding flock
        $damFlock = Flock::updateOrCreate(
            ['flock_number' => '2024-PG-H03-BF01'],
            [
                'species_id'        => $pigId,
                'house_id'          => $house3,
                'breed_variety'     => 'Large White',
                'start_date'        => Carbon::now()->subDays(300),
                'initial_count'     => 25,
                'source'            => 'Breeding Farm',
                'production_type'   => 'meat',
                'is_breeding_stock' => true,
                'sex'               => 'female',
                'parity_number'     => 2,
                'status'            => 'active',
                'created_by'        => 1,
            ]
        );

        // Sire (male) breeding flock
        $sireFlock = Flock::updateOrCreate(
            ['flock_number' => '2024-PG-H03-BM01'],
            [
                'species_id'        => $pigId,
                'house_id'          => $house3,
                'breed_variety'     => 'Large White',
                'start_date'        => Carbon::now()->subDays(400),
                'initial_count'     => 4,
                'source'            => 'Breeding Farm',
                'production_type'   => 'meat',
                'is_breeding_stock' => true,
                'sex'               => 'male',
                'parity_number'     => null,
                'status'            => 'active',
                'created_by'        => 1,
            ]
        );

        // Breeder population snapshots — Flock::breeder_count reads the
        // *latest* flock_breeder_logs row. Without these, breeder_count = 0
        // and resolveEffectiveBreeders() falls back to whole-flock counts.
        FlockBreederLog::updateOrCreate(
            [
                'flock_id' => $damFlock->id,
                'reason'   => 'Initial breeding stock count',
            ],
            [
                'breeder_count'  => 20,
                'sellable_count' => 5,
                'set_by'         => 1,
            ]
        );

        FlockBreederLog::updateOrCreate(
            [
                'flock_id' => $sireFlock->id,
                'reason'   => 'Initial breeding stock count',
            ],
            [
                'breeder_count'  => 4,
                'sellable_count' => 0,
                'set_by'         => 1,
            ]
        );

        $this->command->info('Flocks seeded successfully!');
    }
}