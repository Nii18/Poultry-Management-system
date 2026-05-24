<?php
// database/seeders/FeedDeliverySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedDelivery;
use App\Models\FeedType;
use Carbon\Carbon;

class FeedDeliverySeeder extends Seeder
{
    public function run()
    {
        $chickenStarter = FeedType::where('code', 'CH-STARTER')->first();
        $chickenGrower = FeedType::where('code', 'CH-GROWER')->first();
        $chickenFinisher = FeedType::where('code', 'CH-FINISHER')->first();
        $pigStarter = FeedType::where('code', 'PG-STARTER')->first();

        // Chicken Starter Feed Delivery
        FeedDelivery::updateOrCreate(
            ['invoice_number' => 'INV-2024-001'],
            [
                'feed_type_id' => $chickenStarter->id,
                'supplier_name' => 'AgriFeed Supplies Ltd',
                'quantity_kg' => 5000,
                'cost_per_kg' => 0.85,
                'total_cost' => 4250,
                'delivery_date' => Carbon::now()->subDays(30),
                'expiry_date' => Carbon::now()->addMonths(3),
                'remaining_quantity_kg' => 1200,
                'batch_number' => 'BATCH-CH-001',
                'notes' => 'First delivery of starter feed',
                'received_by' => 1,
            ]
        );

        // Chicken Grower Feed Delivery
        FeedDelivery::updateOrCreate(
            ['invoice_number' => 'INV-2024-002'],
            [
                'feed_type_id' => $chickenGrower->id,
                'supplier_name' => 'AgriFeed Supplies Ltd',
                'quantity_kg' => 8000,
                'cost_per_kg' => 0.82,
                'total_cost' => 6560,
                'delivery_date' => Carbon::now()->subDays(20),
                'expiry_date' => Carbon::now()->addMonths(4),
                'remaining_quantity_kg' => 3500,
                'batch_number' => 'BATCH-CH-002',
                'notes' => 'Grower feed for broilers',
                'received_by' => 1,
            ]
        );

        // Chicken Finisher Feed Delivery (Low Stock)
        FeedDelivery::updateOrCreate(
            ['invoice_number' => 'INV-2024-003'],
            [
                'feed_type_id' => $chickenFinisher->id,
                'supplier_name' => 'Premium Feeds Ltd',
                'quantity_kg' => 6000,
                'cost_per_kg' => 0.88,
                'total_cost' => 5280,
                'delivery_date' => Carbon::now()->subDays(15),
                'expiry_date' => Carbon::now()->addMonths(5),
                'remaining_quantity_kg' => 450, // Low stock alert
                'batch_number' => 'BATCH-CH-003',
                'notes' => 'Finisher feed - running low',
                'received_by' => 1,
            ]
        );

        // Pig Starter Feed Delivery (Expiring Soon)
        FeedDelivery::updateOrCreate(
            ['invoice_number' => 'INV-2024-004'],
            [
                'feed_type_id' => $pigStarter->id,
                'supplier_name' => 'Farmers Choice Feeds',
                'quantity_kg' => 3000,
                'cost_per_kg' => 0.95,
                'total_cost' => 2850,
                'delivery_date' => Carbon::now()->subDays(50),
                'expiry_date' => Carbon::now()->addDays(10), // Expiring soon
                'remaining_quantity_kg' => 800,
                'batch_number' => 'BATCH-PG-001',
                'notes' => 'Expires in 10 days',
                'received_by' => 1,
            ]
        );

        $this->command->info('Feed deliveries seeded successfully!');
    }
}