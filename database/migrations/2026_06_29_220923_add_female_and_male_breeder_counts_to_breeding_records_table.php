<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breeding_records', function (Blueprint $table) {
            // Snapshot of how many female breeders participated at the time of record creation.
            // NULL means the whole flock was used (no breeder subset designated, or mixed flock).
            $table->unsignedInteger('female_breeder_count')->nullable()->after('mate_id');

            // Snapshot of how many male breeders participated at the time of record creation.
            // NULL means AI was used (no male flock) or whole flock was used.
            $table->unsignedInteger('male_breeder_count')->nullable()->after('female_breeder_count');
        });
    }

    public function down(): void
    {
        Schema::table('breeding_records', function (Blueprint $table) {
            $table->dropColumn(['female_breeder_count', 'male_breeder_count']);
        });
    }
};