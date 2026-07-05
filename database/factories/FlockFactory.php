<?php
// database/factories/FlockFactory.php

namespace Database\Factories;

use App\Models\Flock;
use App\Models\FlockBreederLog;
use App\Models\Species;
use App\Models\House;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FlockFactory extends Factory
{
    protected $model = Flock::class;

    public function definition(): array
    {
        $species = Species::inRandomOrder()->first();

        if (!$species) {
            $species = Species::create([
                'code'        => 'CH',
                'name'        => 'Chicken',
                'icon'        => 'twemoji:chicken',
                'color_hex'   => '#F59E0B',
                'description' => 'Default species',
                'is_active'   => true,
            ]);
        }

        $year     = $this->faker->year();
        $sequence = str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

        // FIX: only values that exist in the flocks.production_type ENUM.
        // 'dairy', 'breeding', 'dual_purpose' are NOT in the migration enum
        // and cause MySQL to truncate/error. Use only confirmed valid values.
        $productionType = $this->faker->randomElement(['meat', 'eggs']);

        $isBreedingStock = $this->faker->boolean(20);
        $sex             = $isBreedingStock
            ? $this->faker->randomElement(['male', 'female'])
            : null;

        $startDate    = $this->faker->dateTimeBetween('-180 days', 'now');

        // FIX: only values that exist in the flocks.status ENUM
        // ('active','closed','quarantined','breeding'). 'planned' is NOT
        // a valid enum value and was causing:
        // "SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status'"
        $status       = $this->faker->randomElement(['active', 'closed', 'quarantined']);

        $initialCount = $this->faker->numberBetween(10, 5000);

        return [
            'flock_number'         => $year . '-' . $species->code . '-H'
                                        . str_pad($this->faker->numberBetween(1, 10), 2, '0', STR_PAD_LEFT)
                                        . '-' . $sequence,
            'species_id'           => $species->id,
            'house_id'             => House::factory(),
            'breed_variety'        => $this->faker->randomElement([
                                        'Cobb 500', 'Ross 308', 'ISA Brown', 'Large White',
                                        'Duroc', 'Hereford', 'Angus', 'New Zealand White',
                                        'Landrace', 'Yorkshire', 'Brahman', 'Holstein',
                                     ]),
            'start_date'           => $startDate,
            'end_date'             => $status === 'closed'
                                        ? Carbon::parse($startDate)->addDays($this->faker->numberBetween(28, 90))
                                        : null,
            'initial_count'        => $initialCount,
            'final_count'          => $status === 'closed'
                                        ? $initialCount - $this->faker->numberBetween(0, (int)($initialCount * 0.1))
                                        : null,
            'source'               => $this->faker->randomElement([
                                        'Local Hatchery', 'Breeder Farm', 'Import',
                                        'Own Breeding', 'Commercial Supplier', 'Government Scheme',
                                     ]),
            'production_type'      => $productionType,
            'is_breeding_stock'    => $isBreedingStock,
            'sex'                  => $sex,
            'parity_number'        => ($isBreedingStock && $sex === 'female')
                                        ? $this->faker->numberBetween(1, 5)
                                        : null,
            'status'               => $status,
            'total_weight_kg'      => $status === 'closed' ? $this->faker->numberBetween(1000, 20000) : null,
            'average_price_per_kg' => $status === 'closed' ? $this->faker->randomFloat(2, 1.5, 3.5)   : null,
            'total_revenue'        => $status === 'closed' ? $this->faker->numberBetween(5000, 50000)  : null,
            'created_by'           => User::factory(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }

    // ── Status states ────────────────────────────────────────────────────────

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'active',
            'end_date' => null,
        ]);
    }

    public function closed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'closed',
            'end_date' => Carbon::parse($attributes['start_date'])
                            ->addDays($this->faker->numberBetween(28, 90)),
        ]);
    }

    public function planned(): self
    {
        // FIX: 'planned' is not in the flocks.status ENUM
        // ('active','closed','quarantined','breeding'), so it can't be
        // stored as-is. Mapped to 'active' with a future start_date to
        // preserve the "not started yet" intent without breaking the enum.
        // If you need a true "planned" status, add it to the migration enum
        // instead of faking it here.
        return $this->state(fn (array $attributes) => [
            'status'     => 'active',
            'start_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'end_date'   => null,
        ]);
    }

    public function quarantined(): self
    {
        // Added to match the actual status ENUM — wasn't covered by an
        // explicit state method before.
        return $this->state(fn (array $attributes) => [
            'status'   => 'quarantined',
            'end_date' => null,
        ]);
    }

    // ── Production type states ───────────────────────────────────────────────

    public function meat(): self
    {
        return $this->state(fn (array $attributes) => [
            'production_type'   => 'meat',
            'is_breeding_stock' => false,
            'sex'               => null,
        ]);
    }

    public function eggs(): self
    {
        return $this->state(fn (array $attributes) => [
            'production_type'   => 'eggs',
            'is_breeding_stock' => false,
            'sex'               => null,
        ]);
    }

    // ── Breeding stock states ────────────────────────────────────────────────
    // FIX: production_type set to 'meat' (not 'breeding' — not a valid ENUM value).
    // Breeding stock is identified by is_breeding_stock = true, not production_type.

    public function breedingStock(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_breeding_stock' => true,
            'sex'               => $this->faker->randomElement(['male', 'female']),
            'production_type'   => 'meat', // breeding stock are still meat/egg producers
            'initial_count'     => $this->faker->numberBetween(10, 100),
        ]);
    }

    public function femaleBreeding(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_breeding_stock' => true,
            'sex'               => 'female',
            'parity_number'     => $this->faker->numberBetween(1, 5),
            'production_type'   => 'meat',
            'initial_count'     => $this->faker->numberBetween(10, 50),
        ]);
    }

    public function maleBreeding(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_breeding_stock' => true,
            'sex'               => 'male',
            'parity_number'     => null,
            'production_type'   => 'meat',
            'initial_count'     => $this->faker->numberBetween(2, 10),
        ]);
    }

    // ── Species states ───────────────────────────────────────────────────────

    public function forSpecies(string $speciesCode): self
    {
        $species = Species::where('code', $speciesCode)->first();

        if (!$species) {
            $species = Species::create([
                'code'        => $speciesCode,
                'name'        => match($speciesCode) {
                                     'CH' => 'Chicken', 'PG' => 'Pig',
                                     'CT' => 'Cattle',  'RB' => 'Rabbit',
                                     'GT' => 'Goat',    default => 'Unknown',
                                 },
                'icon'        => 'twemoji:chicken',
                'color_hex'   => '#000000',
                'description' => 'Auto-created species',
                'is_active'   => true,
            ]);
        }

        return $this->state(fn (array $attributes) => [
            'species_id' => $species->id,
        ]);
    }

    public function chicken(): self { return $this->forSpecies('CH'); }
    public function pig(): self     { return $this->forSpecies('PG'); }

    // ── Utility states ───────────────────────────────────────────────────────

    public function withSize(int $count): self
    {
        return $this->state(fn (array $attributes) => [
            'initial_count' => $count,
        ]);
    }

    public function inHouse(int $houseId): self
    {
        return $this->state(fn (array $attributes) => [
            'house_id' => $houseId,
        ]);
    }

    public function startedOn(string $date): self
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $date,
        ]);
    }

    // ── After-creating hooks ─────────────────────────────────────────────────

    public function withBreederLogs(): self
    {
        return $this->afterCreating(function (Flock $flock) {
            if (!$flock->is_breeding_stock) return;

            $breederCount  = $flock->initial_count ?? $this->faker->numberBetween(5, 50);
            $sellableCount = $this->faker->numberBetween(0, (int)($breederCount * 0.2));

            FlockBreederLog::factory()
                ->initialCount()
                ->withCount($breederCount, $sellableCount)
                ->create([
                    'flock_id' => $flock->id,
                    'set_by'   => $flock->created_by ?? User::factory(),
                ]);

            if ($flock->start_date && Carbon::parse($flock->start_date)->diffInDays(now()) > 30) {
                $logCount = $this->faker->numberBetween(2, 5);
                $logs     = FlockBreederLog::factory()
                    ->count($logCount)
                    ->make([
                        'flock_id' => $flock->id,
                        'set_by'   => $flock->created_by ?? User::factory(),
                    ]);

                $startDate = Carbon::parse($flock->start_date);
                $interval  = $startDate->diffInDays(now()) / ($logCount + 1);

                $logs->each(function ($log, $index) use ($startDate, $interval) {
                    $log->created_at = $startDate->copy()->addDays(($index + 1) * $interval);
                    $log->updated_at = $log->created_at;
                    $log->save();
                });
            }
        });
    }

    public function breedingWithCounts(int $breederCount, int $sellableCount = 0): self
    {
        return $this->breedingStock()->afterCreating(function (Flock $flock) use ($breederCount, $sellableCount) {
            $flock->update(['initial_count' => $breederCount + $sellableCount]);

            FlockBreederLog::factory()
                ->initialCount()
                ->withCount($breederCount, $sellableCount)
                ->create([
                    'flock_id' => $flock->id,
                    'set_by'   => $flock->created_by ?? User::factory(),
                ]);
        });
    }

    public function withBreedingSetup(): self
    {
        return $this->afterCreating(function (Flock $flock) {
            if (!$flock->is_breeding_stock) return;

            FlockBreederLog::factory()->count(3)->create([
                'flock_id' => $flock->id,
                'set_by'   => $flock->created_by ?? User::factory(),
            ]);

            if ($flock->sex === 'female') {
                $maleFlock = Flock::where('is_breeding_stock', true)
                    ->where('sex', 'male')
                    ->where('species_id', $flock->species_id)
                    ->first();

                if (!$maleFlock) {
                    $maleFlock = Flock::factory()
                        ->maleBreeding()
                        ->forSpecies($flock->species->code ?? 'CH')
                        ->withBreederLogs()
                        ->create();
                }

                \App\Models\BreedingRecord::factory()->count(3)->create([
                    'flock_id'    => $flock->id,
                    'mate_id'     => $maleFlock->id,
                    'recorded_by' => $flock->created_by ?? User::factory(),
                ]);
            }
        });
    }

    public function withFullHistory(): self
    {
        return $this->active()
            ->withBreederLogs()
            ->afterCreating(function (Flock $flock) {
                if ($flock->start_date) {
                    $days     = Carbon::parse($flock->start_date)->diffInDays(now());
                    $logCount = min($days, 30);

                    for ($i = 1; $i <= $logCount; $i++) {
                        \App\Models\DailyLog::factory()
                            ->forFlock($flock)
                            ->withProduce()
                            ->create([
                                'log_date'   => Carbon::parse($flock->start_date)->addDays($i),
                                'created_by' => $flock->created_by ?? User::factory(),
                            ]);
                    }
                }

                \App\Models\HealthRecord::factory()->count(3)->create([
                    'flock_id'    => $flock->id,
                    'recorded_by' => $flock->created_by ?? User::factory(),
                ]);

                \App\Models\FeedIssuance::factory()->count(5)->create([
                    'flock_id'  => $flock->id,
                    'issued_by' => $flock->created_by ?? User::factory(),
                ]);
            });
    }
}