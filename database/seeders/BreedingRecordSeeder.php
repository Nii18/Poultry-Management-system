<?php
// database/seeders/BreedingRecordSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreedingRecord;
use App\Models\Flock;
use Carbon\Carbon;

class BreedingRecordSeeder extends Seeder
{
    public function run()
    {
        $damFlock  = Flock::where('flock_number', '2024-PG-H03-BF01')->first();
        $sireFlock = Flock::where('flock_number', '2024-PG-H03-BM01')->first();

        if (!$damFlock) {
            $this->command->warn('No breeding-stock female flock found (2024-PG-H03-BF01). Run FlockSeeder first. Skipping.');
            return;
        }

        // Resolve counts the same way the controller does — so seeded data
        // is consistent with real records created through the UI.
        $femaleResolved = BreedingRecord::resolveEffectiveBreeders($damFlock);
        $maleResolved   = $sireFlock
            ? BreedingRecord::resolveEffectiveBreeders($sireFlock)
            : null;

        try {
            // ── Successful natural-mating breeding, delivery already recorded ──
            BreedingRecord::create([
                'flock_id'               => $damFlock->id,
                'mate_id'                => $sireFlock?->id,
                'female_breeder_count'   => $femaleResolved['effective_count'],
                'male_breeder_count'     => $maleResolved ? $maleResolved['effective_count'] : null,
                'breeding_date'          => Carbon::now()->subDays(100),
                'expected_delivery_date' => Carbon::now()->subDays(14),
                'actual_delivery_date'   => Carbon::now()->subDays(14),
                'breeding_method'        => $sireFlock ? 'natural' : 'artificial_insemination',
                'is_successful'          => true,
                'offspring_count'        => 12,
                'stillborn_count'        => 1,
                'weaned_count'           => 10,
                'notes'                  => 'Healthy litter, 12 piglets born',
                'recorded_by'            => 1,
            ]);

            // ── Pending AI breeding — delivery still in the future ──
            // Matches the controller's "pending" scope:
            //   expected_delivery_date > now()  AND  actual_delivery_date IS NULL
            BreedingRecord::create([
                'flock_id'               => $damFlock->id,
                'mate_id'                => null, // External / AI
                'female_breeder_count'   => $femaleResolved['effective_count'],
                'male_breeder_count'     => null, // null for AI records
                'breeding_date'          => Carbon::now()->subDays(60),
                'expected_delivery_date' => Carbon::now()->addDays(54),
                'actual_delivery_date'   => null,
                'breeding_method'        => 'artificial_insemination',
                'is_successful'          => false,
                'offspring_count'        => 0,
                'stillborn_count'        => 0,
                'weaned_count'           => 0,
                'notes'                  => 'Expecting delivery in 54 days',
                'recorded_by'            => 1,
            ]);

            $this->command->info('Breeding records seeded successfully!');

        } catch (\Exception $e) {
            $this->command->error('Error seeding breeding records: ' . $e->getMessage());
        }
    }
}