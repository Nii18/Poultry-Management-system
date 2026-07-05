<?php
// database/factories/TreatmentFactory.php

namespace Database\Factories;

use App\Models\Treatment;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        $flock = Flock::inRandomOrder()->first() ?? Flock::factory()->create();
        $startDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(2, 7));
        $withdrawalDays = $this->faker->numberBetween(3, 14);
        
        $diagnoses = ['Coccidiosis', 'Respiratory Infection', 'Parasites', 'Enteritis', 'Pneumonia'];
        $products = ['Amprolium', 'Tylosin', 'Ivermectin', 'Oxytetracycline', 'Enrofloxacin'];
        
        return [
            'flock_id' => $flock->id,
            'diagnosis' => $this->faker->randomElement($diagnoses),
            'product_name' => $this->faker->randomElement($products),
            'active_ingredient' => $this->faker->words(2, true),
            'dosage' => $this->faker->randomElement([
                '2ml per liter of water',
                '1g per liter of water',
                '10mg per kg body weight',
                '5ml per bird'
            ]),
            'administration_route' => $this->faker->randomElement(['water', 'feed', 'injection', 'topical']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'withdrawal_days' => $withdrawalDays,
            'withdrawal_end_date' => Carbon::parse($endDate)->addDays($withdrawalDays),
            'batch_number' => strtoupper($this->faker->unique()->lexify('???-####')),
            'animals_treated' => $this->faker->numberBetween(50, 1000),
            'cost' => $this->faker->randomFloat(2, 50, 500),
            'notes' => $this->faker->optional()->sentence(),
            'prescribed_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'withdrawal_end_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'withdrawal_end_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 14)),
        ]);
    }
}