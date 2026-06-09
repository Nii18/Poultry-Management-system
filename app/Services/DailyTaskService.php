<?php
// app/Services/DailyTaskService.php

namespace App\Services;

use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use Carbon\Carbon;

class DailyTaskService
{
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