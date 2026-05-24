<?php
// database/migrations/2024_03_26_000007_create_feed_issuances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feed_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained();
            $table->foreignId('feed_delivery_id')->constrained();
            $table->decimal('quantity_kg', 10, 2);
            $table->date('issuance_date');
            $table->time('issuance_time')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('issuance_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feed_issuances');
    }
};