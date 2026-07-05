<?php
// database/factories/SaleFactory.php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Flock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $productTypes = ['eggs', 'live_bird', 'meat', 'milk', 'manure', 'honey', 'wool'];
        $productType = $this->faker->randomElement($productTypes);
        
        $pricing = [
            'eggs' => ['min_qty' => 10, 'max_qty' => 300, 'min_price' => 0.50, 'max_price' => 1.20],
            'live_bird' => ['min_qty' => 1, 'max_qty' => 50, 'min_price' => 35, 'max_price' => 80],
            'meat' => ['min_qty' => 2, 'max_qty' => 30, 'min_price' => 25, 'max_price' => 45],
            'milk' => ['min_qty' => 5, 'max_qty' => 80, 'min_price' => 5, 'max_price' => 9],
            'default' => ['min_qty' => 1, 'max_qty' => 20, 'min_price' => 10, 'max_price' => 50],
        ];
        
        $pricingData = $pricing[$productType] ?? $pricing['default'];
        
        $quantity = round($this->faker->numberBetween(
            $pricingData['min_qty'] * 100,
            $pricingData['max_qty'] * 100
        ) / 100, 2);
        
        $unitPrice = round($this->faker->numberBetween(
            $pricingData['min_price'] * 100,
            $pricingData['max_price'] * 100
        ) / 100, 2);
        
        $totalAmount = round($quantity * $unitPrice, 2);
        
        $saleDate = $this->faker->dateTimeBetween('-365 days', 'now');
        
        return [
            'flock_id' => $this->faker->optional(0.8)->randomElement([null, Flock::factory()->create()->id]),
            'product_type' => $productType,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'sale_date' => $saleDate,
            'customer_name' => $this->faker->optional(0.7)->name(),
            'payment_method' => $this->faker->randomElement(['cash', 'mobile_money', 'bank_transfer']),
            'receipt_number' => 'RCP-' . strtoupper(substr($productType, 0, 4)) . '-' . $saleDate->format('Ym') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'description' => ucfirst(str_replace('_', ' ', $productType)) . ' sale',
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'created_at' => $saleDate,
            'updated_at' => $saleDate,
        ];
    }

    public function eggs(): self
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'eggs',
            'unit_price' => $this->faker->randomFloat(2, 0.50, 1.20),
        ]);
    }

    public function liveBird(): self
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'live_bird',
            'unit_price' => $this->faker->randomFloat(2, 35, 80),
        ]);
    }

    public function meat(): self
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'meat',
            'unit_price' => $this->faker->randomFloat(2, 25, 45),
        ]);
    }
}