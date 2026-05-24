<?php
// database/migrations/2024_03_26_000010_create_health_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained();
            $table->enum('record_type', ['checkup', 'symptom', 'lab_result', 'post_mortem', 'consultation']);
            $table->string('condition')->nullable();
            $table->json('symptoms')->nullable();
            $table->json('lab_results')->nullable();
            $table->text('veterinarian_notes')->nullable();
            $table->integer('affected_count')->nullable();
            $table->string('severity', 20)->nullable();
            $table->date('record_date');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            
            $table->index('record_date');
            $table->index('severity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('health_records');
    }
};