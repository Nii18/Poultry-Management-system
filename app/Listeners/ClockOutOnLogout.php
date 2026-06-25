<?php
// app/Listeners/ClockOutOnLogout.php

namespace App\Listeners;

use App\Models\WorkerAttendance;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;

class ClockOutOnLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (!$user || $user->role !== 'worker') {
            return;
        }

        $today = Carbon::today();

        $attendance = WorkerAttendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Only act if they clocked in and haven't clocked out yet
        if ($attendance && $attendance->clock_in && !$attendance->clock_out) {
            $now = Carbon::now();
            $clockInTime = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->clock_in);
            $hoursWorked = round($clockInTime->diffInMinutes($now) / 60, 2);

            $attendance->update([
                'clock_out'    => $now->format('H:i:s'),
                'hours_worked' => $hoursWorked,
                'notes'        => trim(($attendance->notes ? $attendance->notes . ' | ' : '') . 'Auto clocked-out at logout'),
            ]);
        }
    }
}