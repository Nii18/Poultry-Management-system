<?php
// database/factories/VaccinationFactory.php

namespace Database\Factories;

use App\Models\Vaccination;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class VaccinationFactory extends Factory
{
    protected $model = Vaccination::class;

    public function definition(): array
    {
        $flock = Flock::inRandomOrder()->first() ?? Flock::factory()->create();
        $dayAdministered = $this->faker->numberBetween(1, 30);
        $adminDate = Carbon::parse($flock->start_date)->addDays($dayAdministered);
        
        $vaccines = [
            'Marek\'s Vaccine' => 'Marek\'s Disease',
            'Gumboro Vaccine' => 'Infectious Bursal Disease',
            'Newcastle Disease Vaccine' => 'Newcastle Disease',
            'Infectious Bronchitis Vaccine' => 'Infectious Bronchitis',
            'Fowl Pox Vaccine' => 'Fowl Pox',
            'FMD Vaccine' => 'Foot and Mouth Disease',
        ];
        
        $vaccineName = $this->faker->randomElement(array_keys($vaccines));
        $diseaseTarget = $vaccines[$vaccineName];
        
        return [
            'flock_id' => $flock->id,
            'vaccine_name' => $vaccineName,
            'disease_target' => $diseaseTarget,
            'day_administered' => $dayAdministered,
            'administration_date' => $adminDate,
            'route' => $this->faker->randomElement(['subcutaneous', 'intramuscular', 'drinking_water', 'eye_drop', 'spray']),
            'batch_number' => strtoupper($this->faker->unique()->lexify('???-####')),
            'expiry_date' => $this->faker->dateTimeBetween('+3 months', '+12 months'),
            'dosage_ml' => $this->faker->optional(0.7)->randomFloat(2, 0.05, 2),
            'birds_vaccinated' => $flock->initial_count ?? $this->faker->numberBetween(100, 5000),
            'notes' => $this->faker->optional()->sentence(),
            'administered_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}