<?php
// database/migrations/2024_03_26_000006_create_feed_deliveries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feed_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_type_id')->constrained();
            $table->string('supplier_name');
            $table->string('invoice_number')->nullable();
            $table->decimal('quantity_kg', 10, 2);
            $table->decimal('cost_per_kg', 10, 2);
            $table->decimal('total_cost', 12, 2);
            $table->date('delivery_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('remaining_quantity_kg', 10, 2);
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
            
            $table->index('delivery_date');
            $table->index('expiry_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feed_deliveries');
    }
};