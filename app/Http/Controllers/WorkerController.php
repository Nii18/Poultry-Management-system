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
     */
    public function updateTaskStatus(Request $request, int $id)
    {
        try {
            $assignment = WorkerTaskAssignment::where('id', $id)
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

            $assignment->status = $request->status;

            if ($request->status === 'completed') {
                $assignment->is_completed = true;
                $assignment->completed_at = now();
            } else {
                $assignment->is_completed = false;
                $assignment->completed_at = null;
            }

            $assignment->save();

            return response()->json([
                'success'    => true,
                'message'    => 'Task updated successfully',
                'assignment' => $assignment,
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

        $todayAttendance = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        $isClockedIn = $todayAttendance && !$todayAttendance->clock_out;

        $history = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', '>=', $today->copy()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();

        $stats = [
            'days_worked'  => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'present')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count(),
            'total_hours'  => WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->sum('hours_worked'),
            'on_time_days' => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'present')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count(),
            'late_days'    => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'late')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count(),
        ];

        return view('worker.attendance', compact('todayAttendance', 'isClockedIn', 'history', 'stats'));
    }

    public function clockIn(Request $request)
    {
        try {
            $userId = auth()->id();
            $today  = Carbon::today();
            $now    = Carbon::now();

            $existing = WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', $today)
                ->first();

            if ($existing?->clock_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already clocked in today',
                ], 422);
            }

            $expectedStart = Carbon::parse($today->format('Y-m-d') . ' 08:00:00');
            $status        = $now->lte($expectedStart) ? 'present' : 'late';

            WorkerAttendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $today],
                [
                    'clock_in' => $now->format('H:i:s'),
                    'status'   => $status,
                    'notes'    => $status === 'late' ? 'Arrived late' : null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'time'    => $now->format('h:i A'),
                'status'  => $status,
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
            $userId     = auth()->id();
            $today      = Carbon::today();
            $now        = Carbon::now();

            $attendance = WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance?->clock_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not clocked in yet',
                ], 422);
            }

            if ($attendance->clock_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already clocked out',
                ], 422);
            }

            $clockInTime = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->clock_in);
            $hoursWorked = round($clockInTime->diffInMinutes($now) / 60, 2);

            $attendance->update([
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

            $attendance = WorkerAttendance::where('user_id', $userId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->map(fn($r) => [
                    'date'         => $r->date->format('Y-m-d'),
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