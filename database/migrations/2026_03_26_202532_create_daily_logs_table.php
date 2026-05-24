<?php
// database/migrations/2024_03_26_000004_create_daily_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            
            // Universal metrics
            $table->integer('mortality_count')->default(0);
            $table->integer('culling_count')->default(0);
            $table->decimal('feed_intake_kg', 10, 2)->default(0);
            $table->decimal('water_consumption_liters', 10, 2)->nullable();
            $table->decimal('average_weight_kg', 8, 3)->nullable();
            
            // Species-specific metrics (JSON for flexibility)
            $table->json('species_metrics')->nullable();
            
            // Environmental data
            $table->decimal('min_temperature_c', 5, 2)->nullable();
            $table->decimal('max_temperature_c', 5, 2)->nullable();
            $table->decimal('min_humidity', 5, 2)->nullable();
            $table->decimal('max_humidity', 5, 2)->nullable();
            $table->decimal('ammonia_ppm', 8, 2)->nullable();
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->unique(['flock_id', 'log_date']);
            $table->index('log_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_logs');
    }
};