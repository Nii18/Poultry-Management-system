<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('worker_attendance', function (Blueprint $table) {
            // Drop the constraint that limited a worker to one attendance row
            // per day. Workers can now clock in/out multiple times per day —
            // once per task window (morning/afternoon/evening), and they can
            // even re-clock into the same window more than once if needed.
            $table->dropUnique(['user_id', 'date']);

            // Which task window this session belongs to. Derived automatically
            // from the clock-in time using the same boundaries as
            // DailyTaskService (morning 06:00-12:00, afternoon 12:00-17:00,
            // evening 17:00-22:00). Null when clocking in outside all three
            // windows (e.g. very early morning or late night) — that session
            // simply isn't tied to a window, and is never marked late.
            $table->enum('window', ['morning', 'afternoon', 'evening'])
                ->nullable()
                ->after('date');

            // Non-unique index for the common query pattern: "does this
            // worker already have an open/closed session for this window
            // today" or "show me all of today's sessions for this worker".
            $table->index(['user_id', 'date', 'window']);
        });
    }

    public function down()
    {
        Schema::table('worker_attendance', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date', 'window']);
            $table->dropColumn('window');
            $table->unique(['user_id', 'date']);
        });
    }
};