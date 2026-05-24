<?php
// database/migrations/2024_03_26_000015_create_performance_metrics_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->onDelete('cascade');
            $table->decimal('mortality_rate', 5, 2)->nullable();
            $table->decimal('feed_conversion_ratio', 5, 2)->nullable();
            $table->decimal('average_daily_gain_kg', 6, 3)->nullable();
            $table->decimal('total_feed_consumed_kg', 10, 2)->nullable();
            $table->decimal('total_weight_gained_kg', 10, 2)->nullable();
            $table->decimal('total_revenue', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->decimal('net_profit', 12, 2)->nullable();
            $table->decimal('roi_percentage', 8, 2)->nullable();
            $table->json('species_specific_metrics')->nullable();
            $table->date('calculated_date');
            $table->timestamps();
            
            $table->unique(['flock_id', 'calculated_date']);
            $table->index('calculated_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('performance_metrics');
    }
};