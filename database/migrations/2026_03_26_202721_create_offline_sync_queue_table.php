<?php
// database/migrations/2024_03_26_000018_create_offline_sync_queue_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('offline_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('table_name');
            $table->json('data');
            $table->string('operation_type'); // create, update, delete
            $table->string('record_id')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();
            
            $table->index('synced_at');
            $table->index('attempts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_sync_queue');
    }
};