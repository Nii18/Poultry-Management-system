<?php
// app/Http/Controllers/ManagerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use App\Models\WorkerAttendance;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}
    /**
     * Display task management page
     */
    public function manageTasks()
    {
        $workers = User::where('role', 'worker')->where('is_active', true)->get();
    
        $tasks = WorkerTask::with('assignedTo', 'assignedBy')
            ->whereDate('due_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')  
            ->orderBy('due_date', 'desc')    
            ->paginate(10);
    
        return view('manager.tasks', compact('workers', 'tasks'));
    }

    /**
     * Show create task form
     */
    public function createTaskForm()
    {
        $workers = User::where('role', 'worker')->where('is_active', true)->get();

        return view('manager.create-task', compact('workers'));
    }

    /**
     * Create a new task.
     *
     * IMPORTANT: Creating a WorkerTask row alone does NOT make it appear on a
     * worker's dashboard or tasks page — both of those read from
     * WorkerTaskAssignment, not WorkerTask. This method must always create
     * the corresponding assignment row(s) at the same time, or the task is
     * invisible to the worker (this was the bug: one-off tasks never got an
     * assignment row at all, and recurring tasks only ever got an assignment
     * for "today", regardless of due_date).
     *
     * - One-off task: exactly one WorkerTaskAssignment, dated due_date.
     * - Recurring task (N weeks): "recurring" means every day for N weeks.
     *   One WorkerTask template row is created, plus N*7 WorkerTaskAssignment
     *   rows, one per day from due_date through due_date + (N weeks - 1 day).
     */
    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'priority'           => 'required|in:high,medium,low',
            'due_date'           => 'required|date',
            'start_time'         => 'nullable|date_format:H:i',
            'end_time'           => 'nullable|date_format:H:i|after:start_time',
            'assigned_to'        => 'required|exists:users,id',
            'is_recurring'       => 'boolean',
            'recurring_pattern' => 'nullable|required_if:is_recurring,1|in:daily,weekly,monthly',
            'recurring_weeks'    => 'nullable|integer|min:1|max:12',
        ]);

        // A task due today can't have a start_time that's already passed —
        // the worker has no way to perform it before its own start time, and
        // a task due today starting in the past isn't realistic to assign.
        // Tasks due on a future date are unaffected: "9 AM" is fine for
        // tomorrow even though it's earlier than the current time today.
        $validator->after(function ($validator) use ($request) {
            if (!$request->due_date || !$request->start_time) {
                return;
            }

            $isToday = Carbon::parse($request->due_date)->isToday();

            if ($isToday) {
                $startDateTime = Carbon::parse($request->due_date . ' ' . $request->start_time);

                if ($startDateTime->isPast()) {
                    $validator->errors()->add(
                        'start_time',
                        'Start time cannot be in the past for a task due today.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $isRecurring = $request->boolean('is_recurring');

            $task = WorkerTask::create([
                'title'             => $request->title,
                'description'       => $request->description,
                'priority'          => $request->priority,
                'due_date'          => $request->due_date,
                'start_time'        => $request->start_time,
                'end_time'          => $request->end_time,
                'window'            => $this->deriveWindow($request->start_time),
                'assigned_to'       => $request->assigned_to,
                'assigned_by'       => auth()->id(),
                'is_recurring'      => $isRecurring,
                'recurring_pattern' => $request->recurring_pattern,
                'status'            => 'pending',
            ]);

            if ($isRecurring && $request->recurring_weeks) {
                // "Recurring for N weeks" = one assignment every day for N weeks,
                // all pointing at this single template task.
                $this->generateDailyAssignments($task, $request->recurring_weeks);
            } else {
                // One-off task: a single assignment on its due_date.
                WorkerTaskAssignment::create([
                    'task_id'         => $task->id,
                    'assigned_to'     => $task->assigned_to,
                    'assignment_date' => $task->due_date,
                    'is_completed'    => false,
                    'status'          => 'pending',
                ]);
            }

            DB::commit();

            $this->notifications->notifyWorkerNewTaskAssigned(
                (int) $request->assigned_to,
                $task->title,
                $task->due_date,
                $task->window
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Task created successfully']);
            }

            return redirect()->route('manager.tasks')->with('success', 'Task created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to create task: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to create task: ' . $e->getMessage());
        }
    }

    /**
     * Show edit task form — this was missing, despite being routed to.
     */
    public function editTaskForm($id)
    {
        $task = WorkerTask::findOrFail($id);
        $workers = User::where('role', 'worker')->where('is_active', true)->get();

        return view('manager.edit-task', compact('task', 'workers'));
    }

    /**
     * Update a task.
     *
     * If due_date, start_time, or assigned_to change, any assignment rows
     * still in 'pending' or 'in_progress' are stale (wrong date/worker/
     * window) and are regenerated. Assignments already 'completed' or
     * 'missed' are left untouched — they represent history that already
     * happened and shouldn't be rewritten by a later edit to the template.
     */
    public function editTask(Request $request, $id)
    {
        $task = WorkerTask::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:high,medium,low',
            'due_date'    => 'required|date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i|after:start_time',
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $relevantFieldsChanged = $task->due_date != $request->due_date
                || $task->start_time != $request->start_time
                || $task->assigned_to != $request->assigned_to;

            $task->update([
                'title'       => $request->title,
                'description' => $request->description,
                'priority'    => $request->priority,
                'due_date'    => $request->due_date,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'assigned_to' => $request->assigned_to,
                'window'      => $this->deriveWindow($request->start_time),
            ]);

            if ($relevantFieldsChanged) {
                $this->regenerateStaleAssignments($task);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Task updated successfully']);
            }

            return redirect()->route('manager.tasks')->with('success', 'Task updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update task: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update task: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task.
     *
     * worker_task_assignments.task_id has ON DELETE CASCADE, so deleting the
     * WorkerTask row also deletes all of its assignment rows automatically —
     * no extra cleanup needed here.
     */
    public function deleteTask($id)
    {
        try {
            $task = WorkerTask::findOrFail($id);
            $task->delete();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
            }

            return redirect()->route('manager.tasks')->with('success', 'Task deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete task: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete task: ' . $e->getMessage());
        }
    }

    /**
     * View worker attendance report
     */
    public function viewAttendance()
    {
        $workers = User::where('role', 'worker')->where('is_active', true)->get();
        $selectedWorker = request()->get('worker_id');

        $attendance = collect();
        if ($selectedWorker) {
            $attendance = WorkerAttendance::where('user_id', $selectedWorker)
                ->whereDate('date', '>=', Carbon::now()->subDays(30))
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($record) {
                    $record->is_auto_closed = $this->isAutoClosed($record);
                    return $record;
                });
        }

        // Consistency overview across ALL workers — surfaces repeat offenders
        // even before a manager drills into any individual worker.
        $consistencyOverview = $workers->map(function ($worker) {
            $thirtyDays = WorkerAttendance::where('user_id', $worker->id)
                ->whereDate('date', '>=', Carbon::now()->subDays(30))
                ->get();

            $totalShifts = $thirtyDays->count();
            $autoClosedCount = $thirtyDays->filter(fn($r) => $this->isAutoClosed($r))->count();

            return [
                'worker_id' => $worker->id,
                'name' => $worker->name,
                'total_shifts' => $totalShifts,
                'auto_closed_count' => $autoClosedCount,
                'consistency_rate' => $totalShifts > 0
                    ? round((($totalShifts - $autoClosedCount) / $totalShifts) * 100)
                    : 100,
            ];
        })->sortBy('consistency_rate')->values();

        return view('manager.attendance', compact(
            'workers', 'attendance', 'selectedWorker', 'consistencyOverview'
        ));
    }

    /**
     * Get worker attendance JSON for AJAX
     */
    public function getWorkerAttendance($workerId)
    {
        try {
            $attendance = WorkerAttendance::where('user_id', $workerId)
                ->whereDate('date', '>=', Carbon::now()->subDays(30))
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date->format('d M Y'),
                        'clock_in' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : '--:--',
                        'clock_out' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : '--:--',
                        'hours_worked' => $record->hours_worked ?? 0,
                        'status' => $record->status,
                        'is_auto_closed' => $this->isAutoClosed($record),
                    ];
                });

            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Detect whether a clock-out was system-generated rather than done by the worker.
     */
    private function isAutoClosed(WorkerAttendance $record): bool
    {
        return $record->notes && str_contains($record->notes, 'Auto clocked-out');
    }

    /**
     * Derive a window (morning/afternoon/evening) from a start_time string
     * (H:i format, e.g. "14:30"). Returns null if no start_time was given —
     * matches the fail-open behaviour used everywhere else a null window is
     * encountered (DailyTaskService::isWindowLocked, etc.).
     */
    private function deriveWindow(?string $startTime): ?string
    {
        if (!$startTime) {
            return null;
        }

        $hour = Carbon::parse($startTime)->hour;

        if ($hour >= 6  && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 22) return 'evening';

        // Outside 06:00–22:00 doesn't map to any of the three windows.
        // Leave null rather than guessing — this matches how DailyTaskService
        // treats an unrecognised/missing window (never locked, never grouped).
        return null;
    }

    /**
     * Create one WorkerTaskAssignment per day for $weeks weeks, starting on
     * the task's due_date, all pointing at the same $task (template) row.
     * This is what "recurring" now means: every day, for N weeks.
     */
    private function generateDailyAssignments(WorkerTask $task, int $weeks): void
    {
        $start = Carbon::parse($task->due_date);
        $totalDays = $weeks * 7;

        for ($i = 0; $i < $totalDays; $i++) {
            $date = $start->copy()->addDays($i);

            WorkerTaskAssignment::firstOrCreate(
                [
                    'task_id'         => $task->id,
                    'assigned_to'     => $task->assigned_to,
                    'assignment_date' => $date->format('Y-m-d'),
                ],
                [
                    'is_completed' => false,
                    'status'       => 'pending',
                ]
            );
        }
    }

    /**
     * Regenerate this task's assignment rows after an edit changed due_date,
     * start_time, or assigned_to. Only touches assignments still 'pending' or
     * 'in_progress' — completed/missed rows are left alone since they're
     * history, not a live schedule.
     */
    private function regenerateStaleAssignments(WorkerTask $task): void
    {
        WorkerTaskAssignment::where('task_id', $task->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->delete();

        if ($task->is_recurring) {
            // We don't know how many weeks were originally requested (that
            // value isn't stored on the template), so we can't perfectly
            // regenerate a multi-week recurring schedule here. Re-create a
            // single assignment on the new due_date; any further future days
            // for a recurring template are picked up incrementally by
            // DailyTaskService::generateForWorker() on each day's first load.
            WorkerTaskAssignment::firstOrCreate(
                [
                    'task_id'         => $task->id,
                    'assigned_to'     => $task->assigned_to,
                    'assignment_date' => $task->due_date,
                ],
                [
                    'is_completed' => false,
                    'status'       => 'pending',
                ]
            );
        } else {
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $task->assigned_to,
                'assignment_date' => $task->due_date,
                'is_completed'    => false,
                'status'          => 'pending',
            ]);
        }
    }
}