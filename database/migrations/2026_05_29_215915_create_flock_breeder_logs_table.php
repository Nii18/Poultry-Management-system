<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flock_breeder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->integer('breeder_count')->unsigned();
            $table->integer('sellable_count')->unsigned();   // snapshot: current_count - breeder_count at time of entry
            $table->string('reason')->nullable();            // e.g. "post-selection cull", "added new breeders"
            $table->foreignId('set_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flock_breeder_logs');
    }
};