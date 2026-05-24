<?php
// app/Http/Controllers/ManagerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerAttendance;  // ADD THIS - missing import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  // ADD THIS - for transactions
use Carbon\Carbon;

class ManagerController extends Controller
{
    /**
     * Display task management page
     */
    public function manageTasks()
    {
        $workers = User::where('role', 'worker')->where('is_active', true)->get();
        
        $tasks = WorkerTask::with('assignedTo', 'assignedBy')  // Added 'assignedBy' relationship
            ->whereDate('due_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('due_date', 'desc')
            ->paginate(20);
        
        return view('manager.tasks', compact('workers', 'tasks'));
    }
    
    /**
     * Show create task form (optional)
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'due_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'assigned_to' => 'required|exists:users,id',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'required_if:is_recurring,true|in:daily,weekly,monthly',
            'recurring_weeks' => 'nullable|integer|min:1|max:12'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();  // Now works with import
            
            $task = WorkerTask::create([
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'due_date' => $request->due_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'assigned_to' => $request->assigned_to,
                'assigned_by' => auth()->id(),
                'is_recurring' => $request->is_recurring ?? false,
                'recurring_pattern' => $request->recurring_pattern,
                'status' => 'pending'
            ]);
            
            // If recurring, create additional instances
            if ($request->is_recurring && $request->recurring_weeks) {
                $this->createRecurringTasks($task, $request->recurring_weeks);
            }
            
            DB::commit();
            
            return redirect()->route('manager.tasks')
                ->with('success', 'Task created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create task: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit a task
     */
    public function editTask(Request $request, $id)
    {
        $task = WorkerTask::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'due_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'assigned_to' => 'required|exists:users,id'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            $task->update($request->all());
            
            return redirect()->route('manager.tasks')
                ->with('success', 'Task updated successfully');
        } catch (\Exception $e) {
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
            
            return redirect()->route('manager.tasks')
                ->with('success', 'Task deleted successfully');
        } catch (\Exception $e) {
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
                ->get();
        }
        
        return view('manager.attendance', compact('workers', 'attendance', 'selectedWorker'));
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
                ->map(function($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date->format('d M Y'),
                        'clock_in' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : '--:--',
                        'clock_out' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : '--:--',
                        'hours_worked' => $record->hours_worked ?? 0,
                        'status' => $record->status
                    ];
                });
            
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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