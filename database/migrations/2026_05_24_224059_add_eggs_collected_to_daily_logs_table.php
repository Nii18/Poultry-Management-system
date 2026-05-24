<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->decimal('eggs_collected', 10, 2)->default(0)->after('culling_count');
            $table->decimal('eggs_damaged', 10, 2)->default(0)->after('eggs_collected');
        });
    }
    
    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropColumn(['eggs_collected', 'eggs_damaged']);
        });
    }
};
