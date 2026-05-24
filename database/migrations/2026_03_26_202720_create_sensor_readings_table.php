<?php
// database/migrations/2024_03_26_000017_create_sensor_readings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_device_id')->constrained();
            $table->foreignId('house_id')->constrained();
            $table->decimal('value', 10, 2);
            $table->string('unit', 20);
            $table->datetime('reading_time');
            $table->boolean('is_alert')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('reading_time');
            $table->index('is_alert');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_readings');
    }
};