<?php
// database/migrations/2024_03_26_000009_create_treatments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained();
            $table->string('diagnosis');
            $table->string('product_name');
            $table->string('active_ingredient')->nullable();
            $table->string('dosage');
            $table->enum('administration_route', ['water', 'feed', 'injection', 'topical'])->default('water');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('withdrawal_days')->nullable();
            $table->date('withdrawal_end_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->integer('animals_treated')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('prescribed_by')->constrained('users');
            $table->timestamps();
            
            $table->index('withdrawal_end_date');
            $table->index('start_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('treatments');
    }
};