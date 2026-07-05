<?php
// database/factories/HealthRecordFactory.php

namespace Database\Factories;

use App\Models\HealthRecord;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class HealthRecordFactory extends Factory
{
    protected $model = HealthRecord::class;

    public function definition(): array
    {
        // Must match the enum in create_health_records_table migration exactly
        $recordTypes = ['checkup', 'symptom', 'lab_result', 'post_mortem', 'consultation'];
        $severities = ['info', 'warning', 'critical'];
        $conditions = [
            'Routine Health Check', 'Possible Respiratory Issue',
            'Coccidiosis Outbreak', 'Nutritional Deficiency',
            'Parasite Infestation', 'Injury', 'Stress',
            'Suspected Disease'
        ];

        return [
            'flock_id' => Flock::factory()->active(),
            'record_type' => $this->faker->randomElement($recordTypes),
            'condition' => $this->faker->randomElement($conditions),
            'symptoms' => json_encode([
                'coughing' => $this->faker->boolean(30),
                'sneezing' => $this->faker->boolean(30),
                'lethargy' => $this->faker->boolean(20),
                'reduced_appetite' => $this->faker->boolean(20),
                'diarrhea' => $this->faker->boolean(15),
            ]),
            'lab_results' => $this->faker->optional(0.3)->sentence(),
            'veterinarian_notes' => $this->faker->sentence(),
            'affected_count' => $this->faker->numberBetween(0, 100),
            'severity' => $this->faker->randomElement($severities),
            'record_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'recorded_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function critical(): self
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'critical',
            'affected_count' => $this->faker->numberBetween(50, 200),
        ]);
    }

    public function checkup(): self
    {
        return $this->state(fn (array $attributes) => [
            'record_type' => 'checkup',
            'condition' => 'Routine Health Check',
            'severity' => 'info',
        ]);
    }
}