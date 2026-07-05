<?php
// database/factories/ExpenseFactory.php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Flock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $categories = ['feed', 'veterinary', 'medication', 'labor', 'utilities', 'equipment', 'maintenance'];
        $category = $this->faker->randomElement($categories);
        
        $descriptions = [
            'feed' => ['Chicken Starter Feed', 'Chicken Grower Feed', 'Chicken Finisher Feed', 'Pig Feed'],
            'veterinary' => ['Vaccination Services', 'Health Check', 'Emergency Treatment'],
            'medication' => ['Antibiotics', 'Vitamins', 'Antiparasitics'],
            'labor' => ['Monthly Salary', 'Casual Labor', 'Overtime Payment'],
            'utilities' => ['Electricity Bill', 'Water Bill', 'Fuel'],
            'equipment' => ['Feeders', 'Drinkers', 'Heaters', 'Fans'],
            'maintenance' => ['House Repairs', 'Equipment Maintenance', 'Cleaning Supplies'],
        ];
        
        $paymentMethods = ['cash', 'bank_transfer', 'mobile_money', 'check'];
        
        return [
            'flock_id' => $this->faker->optional(0.7)->randomElement([null, Flock::factory()->create()->id]),
            'category' => $category,
            'description' => $this->faker->randomElement($descriptions[$category] ?? ['General Expense']),
            'amount' => $this->faker->randomFloat(2, 50, 10000),
            'expense_date' => $this->faker->dateTimeBetween('-90 days', 'now'),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'vendor_name' => $this->faker->optional(0.7)->company(),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function feed(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'feed',
            'amount' => $this->faker->randomFloat(2, 1000, 10000),
        ]);
    }

    public function labor(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'labor',
            'amount' => $this->faker->randomFloat(2, 1000, 5000),
            'flock_id' => null,
        ]);
    }
}