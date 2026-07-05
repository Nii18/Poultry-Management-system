<?php
// database/factories/WorkerTaskFactory.php

namespace Database\Factories;

use App\Models\WorkerTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WorkerTaskFactory extends Factory
{
    protected $model = WorkerTask::class;

    public function definition(): array
    {
        $windows = ['morning', 'afternoon', 'evening'];
        $window = $this->faker->randomElement($windows);

        $timeSlots = [
            'morning' => ['start' => '06:00', 'end' => '12:00'],
            'afternoon' => ['start' => '12:00', 'end' => '17:00'],
            'evening' => ['start' => '17:00', 'end' => '20:00'],
        ];

        $tasks = [
            'Morning feeding',
            'Water refill',
            'Health check',
            'Egg collection',
            'House cleaning',
            'Afternoon feeding',
            'Vaccination',
            'Medication administration',
            'Equipment maintenance',
            'Record keeping',
            'Inventory check',
            'Flock inspection',
        ];

        $startTime = $timeSlots[$window]['start'];
        $endTime = $this->faker->time('H:i', $timeSlots[$window]['end']);

        $dueDate = $this->faker->dateTimeBetween('-7 days', '+7 days');

        // Must match the enum in create_worker_tasks_table migration exactly
        $statuses = ['pending', 'in_progress', 'completed'];

        return [
            'title' => $this->faker->randomElement($tasks),
            'description' => $this->faker->sentence(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement($statuses),
            'due_date' => $dueDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'window' => $window,
            'assigned_to' => User::factory()->worker(),
            'assigned_by' => 1,
            'is_recurring' => $this->faker->boolean(30),
            'recurring_pattern' => $this->faker->optional(0.3)->randomElement(['daily', 'weekly', 'monthly']),
            'completed_at' => $this->faker->optional(0.4)->dateTimeBetween('-7 days', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'due_date' => $this->faker->dateTimeBetween('now', '+7 days'),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => Carbon::parse($attributes['due_date'])->addHours($this->faker->numberBetween(1, 8)),
        ]);
    }

    // "Overdue" isn't a real DB status (the enum doesn't have one) — it's a
    // pending task whose due_date has already passed. Represent it that way.
    public function overdue(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => $this->faker->dateTimeBetween('-7 days', '-1 day'),
            'completed_at' => null,
        ]);
    }

    public function recurring(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurring_pattern' => $this->faker->randomElement(['daily', 'weekly']),
        ]);
    }
}