<?php
// database/migrations/2024_03_26_000002_create_houses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('house_code')->unique();
            $table->foreignId('species_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('capacity')->default(0);
            $table->decimal('length_m', 8, 2)->nullable();
            $table->decimal('width_m', 8, 2)->nullable();
            $table->decimal('height_m', 8, 2)->nullable();
            $table->integer('feeders_count')->default(0);
            $table->integer('drinkers_count')->default(0);
            $table->integer('fans_count')->default(0);
            $table->integer('heaters_count')->default(0);
            $table->enum('status', ['active', 'maintenance', 'cleaning', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('houses');
    }
};