<?php
// database/migrations/2024_03_26_000011_create_breeding_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('breeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained();
            $table->foreignId('mate_id')->nullable()->constrained('flocks');
            $table->date('breeding_date');
            $table->date('expected_delivery_date');
            $table->date('actual_delivery_date')->nullable();
            $table->enum('breeding_method', ['natural', 'artificial_insemination'])->default('natural');
            $table->boolean('is_successful')->default(false);
            $table->integer('offspring_count')->nullable();
            $table->integer('stillborn_count')->default(0);
            $table->integer('weaned_count')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            
            $table->index('breeding_date');
            $table->index('expected_delivery_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('breeding_records');
    }
};