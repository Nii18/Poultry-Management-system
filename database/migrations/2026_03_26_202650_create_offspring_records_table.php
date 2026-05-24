<?php
// database/migrations/2024_03_26_000012_create_offspring_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('offspring_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breeding_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('new_flock_id')->nullable()->constrained('flocks');
            $table->integer('count');
            $table->decimal('average_birth_weight_kg', 6, 2)->nullable();
            $table->string('ear_tag_prefix')->nullable();
            $table->integer('ear_tag_start_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offspring_records');
    }
};