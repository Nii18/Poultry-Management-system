<?php
// database/migrations/2024_03_26_000008_create_vaccinations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained();
            $table->string('vaccine_name');
            $table->string('disease_target');
            $table->integer('day_administered');
            $table->date('administration_date');
            $table->enum('route', ['subcutaneous', 'intramuscular', 'drinking_water', 'spray', 'eye_drop'])->default('drinking_water');
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->decimal('dosage_ml', 8, 2)->nullable();
            $table->integer('birds_vaccinated')->nullable();
            $table->foreignId('administered_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('administration_date');
            $table->index('day_administered');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vaccinations');
    }
};