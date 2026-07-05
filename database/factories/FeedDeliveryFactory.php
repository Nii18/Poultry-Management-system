<?php
// database/factories/FeedDeliveryFactory.php

namespace Database\Factories;

use App\Models\FeedDelivery;
use App\Models\FeedType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FeedDeliveryFactory extends Factory
{
    protected $model = FeedDelivery::class;

    public function definition(): array
    {
        $feedType = FeedType::inRandomOrder()->first() ?? FeedType::factory()->create();
        $quantity = $this->faker->numberBetween(1000, 10000);
        $costPerKg = $this->faker->randomFloat(2, 0.5, 1.5);
        
        return [
            'feed_type_id' => $feedType->id,
            'supplier_name' => $this->faker->randomElement([
                'AgriFeed Supplies Ltd', 'Premium Feeds Ltd', 
                'Farmers Choice Feeds', 'Quality Feed Co',
                'Local Feed Mill'
            ]),
            'invoice_number' => 'INV-' . $this->faker->year() . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'quantity_kg' => $quantity,
            'cost_per_kg' => $costPerKg,
            'total_cost' => round($quantity * $costPerKg, 2),
            'delivery_date' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'remaining_quantity_kg' => $this->faker->numberBetween(100, $quantity),
            'batch_number' => 'BATCH-' . strtoupper($this->faker->unique()->lexify('???-###')),
            'notes' => $this->faker->optional()->sentence(),
            'received_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function lowStock(): self
    {
        return $this->state(fn (array $attributes) => [
            'remaining_quantity_kg' => $this->faker->numberBetween(10, 100),
        ]);
    }

    public function expiringSoon(): self
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('+1 day', '+10 days'),
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}