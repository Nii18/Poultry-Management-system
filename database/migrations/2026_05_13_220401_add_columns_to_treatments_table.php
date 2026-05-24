<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('treatments', function (Blueprint $table) {
            // Add status column (for tracking treatment progress)
            if (!Schema::hasColumn('treatments', 'status')) {
                $table->string('status')->default('pending')->after('prescribed_by');
            }
            
            // Add treatment_type column (to distinguish between treatment and vaccination)
            if (!Schema::hasColumn('treatments', 'treatment_type')) {
                $table->string('treatment_type')->default('treatment')->after('status');
            }
            
            // Add next_due_date column (for vaccination schedules)
            if (!Schema::hasColumn('treatments', 'next_due_date')) {
                $table->date('next_due_date')->nullable()->after('end_date');
            }
        });
    }

    public function down()
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (Schema::hasColumn('treatments', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('treatments', 'treatment_type')) {
                $table->dropColumn('treatment_type');
            }
            if (Schema::hasColumn('treatments', 'next_due_date')) {
                $table->dropColumn('next_due_date');
            }
        });
    }
};