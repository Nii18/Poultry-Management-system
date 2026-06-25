<?php
// app/Console/Commands/AutoCloseAttendance.php

namespace App\Console\Commands;

use App\Models\WorkerAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseAttendance extends Command
{
    protected $signature = 'attendance:auto-close';
    protected $description = 'Auto clock-out any worker still clocked in at end of day';

    public function handle(): int
    {
        $today = Carbon::today();
        $cutoff = Carbon::now(); // job runs at the cutoff time, e.g. 22:00

        $stillOpen = WorkerAttendance::whereDate('date', $today)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->get();

        foreach ($stillOpen as $attendance) {
            $clockInTime = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->clock_in);
            $hoursWorked = round($clockInTime->diffInMinutes($cutoff) / 60, 2);

            $attendance->update([
                'clock_out'    => $cutoff->format('H:i:s'),
                'hours_worked' => $hoursWorked,
                'notes'        => trim(($attendance->notes ? $attendance->notes . ' | ' : '') . 'Auto clocked-out by system — worker did not clock out'),
            ]);
        }

        $this->info("Auto-closed {$stillOpen->count()} attendance record(s).");
        return self::SUCCESS;
    }
}