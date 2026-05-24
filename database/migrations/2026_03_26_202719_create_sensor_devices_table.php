<?php
// database/migrations/2024_03_26_000016_create_sensor_devices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sensor_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained();
            $table->string('device_id')->unique();
            $table->string('device_name');
            $table->enum('sensor_type', ['temperature', 'humidity', 'ammonia', 'weight', 'feed_level', 'water_flow']);
            $table->string('api_key')->unique();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->datetime('last_reading_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('sensor_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_devices');
    }
};