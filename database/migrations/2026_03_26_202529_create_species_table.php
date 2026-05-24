<?php
// database/migrations/2024_03_26_000001_create_species_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 5)->unique();
            $table->string('icon')->nullable();
            $table->string('color_hex', 7)->default('#3B82F6');
            $table->text('description')->nullable();
            $table->json('default_metrics')->nullable();
            $table->json('growth_standards')->nullable();
            $table->json('health_indicators')->nullable();
            $table->integer('gestation_days')->nullable();
            $table->integer('weaning_days')->nullable();
            $table->integer('market_age_days')->nullable();
            $table->decimal('market_weight_kg', 8, 2)->nullable();
            $table->integer('lifespan_years')->nullable();
            $table->integer('sexual_maturity_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('species');
    }
};