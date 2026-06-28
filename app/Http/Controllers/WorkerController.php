<?php
// app/Http/Controllers/WorkerController.php

namespace App\Http\Controllers;

use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use App\Models\WorkerAttendance;
use App\Services\DailyTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WorkerController extends Controller
{
    public function __construct(protected DailyTaskService $taskService) {}

    // ── Tasks ─────────────────────────────────────────────────────────────────

    public function tasks()
    {
        $userId = auth()->id();

        // Auto-generate today's assignments from recurring templates (idempotent)
        $this->taskService->generateForWorker($userId);

        // Auto-mark past-window tasks as missed
        $this->taskService->markMissedForWorker($userId);

        $grouped        = $this->taskService->getGroupedAssignments($userId);
        $currentWindow  = $this->taskService->currentWindow();
        $allAssignments = collect($grouped)->flatten();

        $stats = [
            'total'           => $allAssignments->count(),
            'completed'       => $allAssignments->where('status', 'completed')->count(),
            'missed'          => $allAssignments->where('status', 'missed')->count(),
            'completion_rate' => $this->calculateCompletionRate($userId),
        ];

        return view('worker.tasks', compact('grouped', 'currentWindow', 'stats'));
    }

    /**
     * Update a WorkerTaskAssignment status.
     * Workers call this by assignment ID, not task ID.
     *
     * Accepts status: pending | in_progress | completed
     * Accepts undo: true (re-opens a completed task within the grace period)
     */
    public function updateTaskStatus(Request $request, int $id)
    {
        try {
            $assignment = WorkerTaskAssignment::with('task')
                ->where('id', $id)
                ->where('assigned_to', auth()->id())
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $newStatus = $request->status;

            // Block completing a task whose time window hasn't opened yet.
            // This is the authoritative check — the UI hides/disables the
            // checkbox for locked tasks, but that's purely cosmetic without
            // this server-side guard (a raw fetch() call could otherwise
            // bypass it from either the dashboard or the tasks page).
            if ($newStatus === 'completed') {
                $isLocked = $this->taskService->isWindowLocked($assignment->task?->window);

                if ($isLocked) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This task is locked until its time window opens.',
                    ], 422);
                }
            }

            // Grace-period enforcement for undo (reverting from completed → pending)
            // Allow undo only within 15 minutes of completion
            if ($newStatus !== 'completed' && $assignment->status === 'completed') {
                $gracePeriodExpired = $assignment->completed_at
                    && $assignment->completed_at->lt(now()->subMinutes(15));

                if ($gracePeriodExpired) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Undo window has expired (15 minutes after completion).',
                    ], 422);
                }
            }

            $assignment->status = $newStatus;

            if ($newStatus === 'completed') {
                $assignment->is_completed = true;
                $assignment->completed_at = now();
            } else {
                $assignment->is_completed = false;
                $assignment->completed_at = null;
            }

            $assignment->save();

            // Return completed_at so the frontend can show/hide the undo button
            return response()->json([
                'success'      => true,
                'message'      => 'Task updated successfully',
                'assignment'   => [
                    'id'           => $assignment->id,
                    'status'       => $assignment->status,
                    'is_completed' => $assignment->is_completed,
                    'completed_at' => $assignment->completed_at?->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function attendance()
    {
        $userId = auth()->id();
        $today  = Carbon::today();

        // "Currently clocked in" now means "has an open session" (any
        // window), not "has a row for today" — there may be earlier,
        // already-closed sessions for today as well.
        $openSession = WorkerAttendance::where('user_id', $userId)
            ->openSession()
            ->first();

        $isClockedIn = (bool) $openSession;

        // History is now one row per SESSION, most recent first.
        $history = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', '>=', $today->copy()->subDays(30))
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->get();

        $monthSessions = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', '>=', $today->copy()->startOfMonth())
            ->get();

        $stats = [
            // Distinct CALENDAR DAYS with at least one 'present' session —
            // counting sessions directly would inflate this once a worker
            // can have 2-3 sessions in the same day.
            'days_worked'  => $monthSessions->where('status', 'present')
                ->pluck('date')
                ->map(fn($d) => $d->format('Y-m-d'))
                ->unique()
                ->count(),

            // Hours add up correctly across sessions with a plain sum —
            // no day-deduplication needed here.
            'total_hours'  => $monthSessions->sum('hours_worked'),

            'on_time_days' => $monthSessions->where('status', 'present')
                ->pluck('date')
                ->map(fn($d) => $d->format('Y-m-d'))
                ->unique()
                ->count(),

            // Distinct days with at least one late session, not a count of
            // late sessions — a worker late for 2 of 3 windows in one day
            // should still only count as 1 late day.
            'late_days'    => $monthSessions->where('status', 'late')
                ->pluck('date')
                ->map(fn($d) => $d->format('Y-m-d'))
                ->unique()
                ->count(),
        ];

        return view('worker.attendance', compact('openSession', 'isClockedIn', 'history', 'stats'));
    }

    public function clockIn(Request $request)
    {
        try {
            $userId = auth()->id();
            $today  = Carbon::today();
            $now    = Carbon::now();

            // A worker can only be in ONE open session at a time, but may
            // already have earlier closed sessions today (e.g. morning
            // already clocked in/out) — that's expected and allowed.
            $openSession = WorkerAttendance::where('user_id', $userId)
                ->openSession()
                ->first();

            if ($openSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already clocked in. Clock out before starting a new session.',
                ], 422);
            }

            // Which task window does this clock-in fall into? Null when
            // outside all three windows (e.g. very early morning or late
            // night) — that session just isn't tied to a window and is
            // never marked late.
            $window = $this->taskService->windowForTime($now);
            $window = $window === 'none' ? null : $window;

            // Lateness cutoff matches each window's own opening time —
            // morning/afternoon/evening all use the same 6/12/17 boundaries
            // as DailyTaskService, so "late" means "after this window had
            // already opened", not a single fixed cutoff for the whole day.
            $windowStarts = [
                'morning'   => '06:00:00',
                'afternoon' => '12:00:00',
                'evening'   => '17:00:00',
            ];

            $status = 'present';
            if ($window && isset($windowStarts[$window])) {
                $expectedStart = Carbon::parse($today->format('Y-m-d') . ' ' . $windowStarts[$window]);
                $status = $now->gt($expectedStart) ? 'late' : 'present';
            }

            $session = WorkerAttendance::create([
                'user_id' => $userId,
                'date'    => $today,
                'window'  => $window,
                'clock_in' => $now->format('H:i:s'),
                'status'   => $status,
                'notes'    => $status === 'late' ? 'Arrived late' : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'time'    => $now->format('h:i A'),
                'status'  => $status,
                'window'  => $window,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function clockOut(Request $request)
    {
        try {
            $userId = auth()->id();
            $now    = Carbon::now();

            // Close whichever session is currently open, regardless of which
            // window it belongs to or what day it started on (a session
            // should always be closed same-day in practice, but we look up
            // by open-session rather than by today's date to stay correct
            // even if a session is somehow left open overnight).
            $session = WorkerAttendance::where('user_id', $userId)
                ->openSession()
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not clocked in yet',
                ], 422);
            }

            $clockInTime = Carbon::parse($session->date->format('Y-m-d') . ' ' . $session->clock_in);
            $hoursWorked = round($clockInTime->diffInMinutes($now) / 60, 2);

            $session->update([
                'clock_out'    => $now->format('H:i:s'),
                'hours_worked' => $hoursWorked,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Clocked out successfully',
                'time'         => $now->format('h:i A'),
                'hours_worked' => $hoursWorked,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAttendanceData(Request $request)
    {
        try {
            $userId = auth()->id();
            $month  = $request->get('month', Carbon::now()->month);
            $year   = $request->get('year', Carbon::now()->year);

            // One entry per SESSION (not per day) — a day with 3 sessions
            // now produces 3 entries here, each carrying its own window.
            $attendance = WorkerAttendance::where('user_id', $userId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')
                ->orderBy('clock_in')
                ->get()
                ->map(fn($r) => [
                    'date'         => $r->date->format('Y-m-d'),
                    'window'       => $r->window,
                    'clock_in'     => $r->clock_in  ? Carbon::parse($r->clock_in)->format('h:i A')  : null,
                    'clock_out'    => $r->clock_out ? Carbon::parse($r->clock_out)->format('h:i A') : null,
                    'hours_worked' => $r->hours_worked,
                    'status'       => $r->status,
                ]);

            return response()->json(['success' => true, 'attendance' => $attendance]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function help()
    {
        return view('worker.help');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function calculateCompletionRate(int $userId): int
    {
        $total = WorkerTaskAssignment::forUser($userId)
            ->whereDate('assignment_date', '>=', Carbon::now()->subDays(30))
            ->count();

        $completed = WorkerTaskAssignment::forUser($userId)
            ->where('status', 'completed')
            ->whereDate('assignment_date', '>=', Carbon::now()->subDays(30))
            ->count();

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
}