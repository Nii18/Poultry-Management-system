<?php
// database/migrations/2024_03_26_000003_create_flocks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained()->onDelete('restrict');
            $table->foreignId('house_id')->constrained()->onDelete('restrict');
            $table->string('flock_number')->unique();
            $table->string('breed_variety');
            $table->date('start_date');
            $table->integer('initial_count');
            $table->integer('current_count')->nullable();
            $table->string('source')->nullable();
            $table->enum('production_type', ['meat', 'eggs', 'milk', 'breeding', 'dual_purpose'])->default('meat');
            $table->boolean('is_breeding_stock')->default(false);
            $table->integer('parity_number')->nullable();
            $table->date('last_breeding_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['active', 'closed', 'quarantined', 'breeding'])->default('active');
            $table->date('end_date')->nullable();
            $table->integer('final_count')->nullable();
            $table->decimal('total_weight_kg', 10, 2)->nullable();
            $table->decimal('average_price_per_kg', 10, 2)->nullable();
            $table->decimal('total_revenue', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['species_id', 'status']);
            $table->index('flock_number');
            $table->index('production_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flocks');
    }
};