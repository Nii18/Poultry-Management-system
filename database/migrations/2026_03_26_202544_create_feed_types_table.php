<?php
// database/migrations/2024_03_26_000005_create_feed_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feed_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('category', ['starter', 'grower', 'finisher', 'layer', 'breeder', 'maintenance'])->default('grower');
            $table->decimal('protein_percentage', 5, 2)->nullable();
            $table->decimal('energy_mj_kg', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feed_types');
    }
};