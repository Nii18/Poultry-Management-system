<?php
// app/Services/DailyTaskService.php

namespace App\Services;

use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use Carbon\Carbon;

class DailyTaskService
{
    /**
     * Numeric order for window comparison. Used to determine whether a given
     * window is still locked relative to the current window.
     */
    private const WINDOW_ORDER = ['morning' => 0, 'afternoon' => 1, 'evening' => 2];

    /**
     * Generate today's assignment rows for a worker from all active recurring
     * task templates. Uses firstOrCreate — completely idempotent, safe to call
     * on every page load without creating duplicates.
     */
    public function generateForWorker(int $userId): void
    {
        $today     = Carbon::today();
        $templates = WorkerTask::where('is_recurring', true)->get();

        foreach ($templates as $template) {
            WorkerTaskAssignment::firstOrCreate(
                [
                    'task_id'         => $template->id,
                    'assigned_to'     => $userId,
                    'assignment_date' => $today,
                ],
                [
                    'is_completed' => false,
                    'status'       => 'pending',
                ]
            );
        }
    }

   /**
 * Mark assignments as 'missed' only when their WINDOW has fully closed,
 * not when the individual task end_time passes.
 */
public function markMissedForWorker(int $userId): void
{
    $now   = Carbon::now();
    $today = Carbon::today();

    // Window closing times — matches currentWindow() logic
    $windowEnds = [
        'morning'   => Carbon::parse($today->format('Y-m-d') . ' 12:00:00'),
        'afternoon' => Carbon::parse($today->format('Y-m-d') . ' 17:00:00'),
        'evening'   => Carbon::parse($today->format('Y-m-d') . ' 22:00:00'),
    ];

    WorkerTaskAssignment::with('task')
        ->forUser($userId)
        ->forToday()
        ->whereIn('status', ['pending', 'in_progress'])
        ->get()
        ->each(function ($assignment) use ($now, $windowEnds) {
            $window = $assignment->task?->window;

            if (!$window || !isset($windowEnds[$window])) return;

            // Only miss it when the whole window is over
            if ($now->gt($windowEnds[$window])) {
                $assignment->update(['status' => 'missed']);
            }
        });
}

  /**
     * Determine which window a specific time falls into.
     * 
     * @param Carbon $time
     * @return string 'morning' | 'afternoon' | 'evening' | 'none'
     */
    public function windowForTime(Carbon $time): string
    {
        $hour = $time->hour;

        if ($hour >= 6  && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 22) return 'evening';

        return 'none';
    }

    
    /**
     * Return the current time window name based on the server clock.
     */
    public function currentWindow(): string
    {
        $hour = Carbon::now()->hour;

        if ($hour >= 6  && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 22) return 'evening';

        return 'none';
    }

    /**
     * Is the given task window still locked (i.e. it hasn't opened yet today)?
     *
     * Used both to render lock icons in the UI and to reject status-update
     * requests server-side for tasks whose window hasn't opened.
     *
     * Fails OPEN (returns false / not locked) when $window is null or
     * unrecognised — matches the existing behaviour in markMissedForWorker(),
     * which also skips tasks with no window rather than blocking them.
     */
    public function isWindowLocked(?string $window): bool
    {
        if (!$window || !isset(self::WINDOW_ORDER[$window])) {
            return false;
        }

        $current = $this->currentWindow();

        // Outside all working hours ('none') → nothing is open → treat as locked
        if ($current === 'none' || !isset(self::WINDOW_ORDER[$current])) {
            return true;
        }

        return self::WINDOW_ORDER[$window] > self::WINDOW_ORDER[$current];
    }

    /**
     * Return today's assignments for a worker, grouped by window, sorted by
     * start_time within each group.
     *
     * @return array{morning: \Illuminate\Support\Collection, afternoon: \Illuminate\Support\Collection, evening: \Illuminate\Support\Collection}
     */
    public function getGroupedAssignments(int $userId): array
    {
        $assignments = WorkerTaskAssignment::with('task')
            ->forUser($userId)
            ->forToday()
            ->get()
            ->sortBy(fn($a) => $a->task?->start_time);

        return [
            'morning'   => $assignments->filter(fn($a) => $a->task?->window === 'morning'),
            'afternoon' => $assignments->filter(fn($a) => $a->task?->window === 'afternoon'),
            'evening'   => $assignments->filter(fn($a) => $a->task?->window === 'evening'),
        ];
    }
}