<?php
// app/Http/Controllers/ManagerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    /**
     * Display task management page
     */
    public function manageTasks()
    {
        $workers = User::where('role', 'worker')->where('is_active', true)->get();

        $tasks = WorkerTask::with('assignedTo', 'assignedBy')
            ->whereDate('due_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('due_date', 'desc')
            ->paginate(20);

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
     * Create a new task
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

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        $task = WorkerTask::create([
            'title'             => $request->title,
            'description'       => $request->description,
            'priority'          => $request->priority,
            'due_date'          => $request->due_date,
            'start_time'        => $request->start_time,
            'end_time'          => $request->end_time,
            'assigned_to'       => $request->assigned_to,
            'assigned_by'       => auth()->id(),
            'is_recurring'      => $request->boolean('is_recurring'),
            'recurring_pattern' => $request->recurring_pattern,
            'status'            => 'pending',
        ]);

        if ($request->boolean('is_recurring') && $request->recurring_weeks) {
            $this->createRecurringTasks($task, $request->recurring_weeks);
        }

        DB::commit();

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
     * Update a task
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
        $task->update($request->only([
            'title', 'description', 'priority', 'due_date',
            'start_time', 'end_time', 'assigned_to',
        ]));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Task updated successfully']);
        }

        return redirect()->route('manager.tasks')->with('success', 'Task updated successfully');

    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to update task: ' . $e->getMessage()], 500);
        }
        return back()->with('error', 'Failed to update task: ' . $e->getMessage());
    }
}

    /**
     * Delete a task
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
     * Create recurring tasks
     */
    private function createRecurringTasks($originalTask, $weeks)
    {
        for ($i = 1; $i <= $weeks; $i++) {
            $newDueDate = Carbon::parse($originalTask->due_date)->addWeeks($i);

            WorkerTask::create([
                'title' => $originalTask->title,
                'description' => $originalTask->description,
                'priority' => $originalTask->priority,
                'due_date' => $newDueDate,
                'start_time' => $originalTask->start_time,
                'end_time' => $originalTask->end_time,
                'assigned_to' => $originalTask->assigned_to,
                'assigned_by' => $originalTask->assigned_by,
                'is_recurring' => false,
                'status' => 'pending'
            ]);
        }
    }
}