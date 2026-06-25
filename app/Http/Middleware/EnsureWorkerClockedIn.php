<?php
// app/Http/Middleware/EnsureWorkerClockedIn.php

namespace App\Http\Middleware;

use App\Models\WorkerAttendance;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class EnsureWorkerClockedIn
{
    /**
     * Routes a worker can always reach, even before clocking in.
     */
    protected array $exemptRouteNames = [
        'worker.attendance',
        'worker.clock-in',
        'worker.clock-out',
        'worker.attendance-data',
        'worker.help',
        'logout',
        'account.edit',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Only enforce this for the 'worker' role
        if (!$user || $user->role !== 'worker') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (in_array($routeName, $this->exemptRouteNames, true)) {
            return $next($request);
        }

        $hasClockedInToday = WorkerAttendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('clock_in')
            ->exists();

        if (!$hasClockedInToday) {
            return redirect()
                ->route('worker.attendance')
                ->with('warning', "Please clock in first — your supervisor needs to know you're on site today before you can continue.");
        }

        return $next($request);
    }
}