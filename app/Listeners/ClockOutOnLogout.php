<?php

namespace App\Listeners;

use App\Models\WorkerAttendance;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class ClockOutOnLogout
{
    // Roles that participate in attendance tracking
    private const ATTENDED_ROLES = ['worker', 'manager', 'head_worker'];

    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (!$user || !in_array($user->role, self::ATTENDED_ROLES)) {
            return;
        }

        $attendance = WorkerAttendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (!$attendance) {
            return;
        }

        $now = Carbon::now();
        $clockIn = Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $attendance->clock_in);
        $hoursWorked = round($clockIn->diffInMinutes($now) / 60, 2);

        $attendance->update([
            'clock_out'    => $now->format('H:i:s'),
            'hours_worked' => $hoursWorked,
            'notes'        => trim(($attendance->notes ? $attendance->notes . ' | ' : '') . 'Auto clocked-out at logout'),
        ]);

        Log::info('Auto clock-out triggered', [
            'user_id'      => $user->id,
            'role'         => $user->role,
            'clock_out'    => $now->format('H:i:s'),
            'hours_worked' => $hoursWorked,
        ]);
    }
}