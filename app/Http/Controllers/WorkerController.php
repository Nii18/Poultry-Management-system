<?php
// app/Http/Controllers/WorkerController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkerController extends Controller
{
    /**
     * Display worker tasks dashboard
     */
    public function tasks()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        
        // Get today's tasks
        $todayTasks = WorkerTask::where('assigned_to', $userId)
            ->whereDate('due_date', $today)
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Get pending tasks (overdue)
        $pendingTasks = WorkerTask::where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Get upcoming tasks (next 7 days)
        $upcomingTasks = WorkerTask::where('assigned_to', $userId)
            ->whereDate('due_date', '>', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Get completed tasks (last 30 days)
        $completedTasks = WorkerTask::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->whereDate('completed_at', '>=', $today->copy()->subDays(30))
            ->orderBy('completed_at', 'desc')
            ->get();
        
        $stats = [
            'total_today' => $todayTasks->count(),
            'completed_today' => $todayTasks->where('status', 'completed')->count(),
            'pending_count' => $pendingTasks->count(),
            'upcoming_count' => $upcomingTasks->count(),
            'completion_rate' => $this->calculateCompletionRate($userId)
        ];
        
        return view('worker.tasks', compact('todayTasks', 'pendingTasks', 'upcomingTasks', 'completedTasks', 'stats'));
    }
    
    /**
     * Update task status (complete/incomplete)
     */
    public function updateTaskStatus(Request $request, $id)
    {
        try {
            $task = WorkerTask::where('id', $id)
                ->where('assigned_to', auth()->id())
                ->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            
            $task->status = $request->status;
            if ($request->status === 'completed') {
                $task->completed_at = now();
            }
            $task->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Display attendance dashboard
     */
    public function attendance()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        
        // Get today's attendance record
        $todayAttendance = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();
        
        // Check if currently clocked in (no clock_out for today)
        $isClockedIn = $todayAttendance && !$todayAttendance->clock_out;
        
        // Get attendance history (last 30 days)
        $history = WorkerAttendance::where('user_id', $userId)
            ->whereDate('date', '>=', $today->copy()->subDays(30))
            ->orderBy('date', 'desc')
            ->get();
        
        // Calculate stats
        $stats = [
            'days_worked' => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'present')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count(),
            'total_hours' => WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->sum('hours_worked'),
            'on_time_days' => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'present')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count(),
            'late_days' => WorkerAttendance::where('user_id', $userId)
                ->where('status', 'late')
                ->whereDate('date', '>=', $today->copy()->startOfMonth())
                ->count()
        ];
        
        return view('worker.attendance', compact('todayAttendance', 'isClockedIn', 'history', 'stats'));
    }
    
    /**
     * Clock in for the day
     */
    public function clockIn(Request $request)
    {
        try {
            $userId = auth()->id();
            $today = Carbon::today();
            $now = Carbon::now();
            
            // Check if already clocked in today
            $existing = WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', $today)
                ->first();
            
            if ($existing && $existing->clock_in) {
                return response()->json(['success' => false, 'message' => 'Already clocked in today'], 422);
            }
            
            // Determine status (on time or late)
            $expectedStartTime = Carbon::parse($today->format('Y-m-d') . ' 08:00:00');
            $status = $now <= $expectedStartTime ? 'present' : 'late';
            
            $attendance = WorkerAttendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $today],
                [
                    'clock_in' => $now->format('H:i:s'),
                    'status' => $status,
                    'notes' => $status === 'late' ? 'Arrived late' : null
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully',
                'time' => $now->format('h:i A'),
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Clock out for the day
     */
    public function clockOut(Request $request)
    {
        try {
            $userId = auth()->id();
            $today = Carbon::today();
            $now = Carbon::now();
            
            $attendance = WorkerAttendance::where('user_id', $userId)
                ->whereDate('date', $today)
                ->first();
            
            if (!$attendance || !$attendance->clock_in) {
                return response()->json(['success' => false, 'message' => 'Not clocked in yet'], 422);
            }
            
            if ($attendance->clock_out) {
                return response()->json(['success' => false, 'message' => 'Already clocked out'], 422);
            }
            
            // Calculate hours worked
            $clockInTime = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->clock_in);
            $hoursWorked = round($clockInTime->diffInMinutes($now) / 60, 2);
            
            $attendance->clock_out = $now->format('H:i:s');
            $attendance->hours_worked = $hoursWorked;
            $attendance->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully',
                'time' => $now->format('h:i A'),
                'hours_worked' => $hoursWorked
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get attendance data for AJAX
     */
    public function getAttendanceData(Request $request)
    {
        try {
            $userId = auth()->id();
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            
            $attendance = WorkerAttendance::where('user_id', $userId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->map(function($record) {
                    return [
                        'date' => $record->date->format('Y-m-d'),
                        'clock_in' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : null,
                        'clock_out' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : null,
                        'hours_worked' => $record->hours_worked,
                        'status' => $record->status
                    ];
                });
            
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Display help page
     */
    public function help()
    {
        return view('worker.help');
    }
    
    /**
     * Calculate task completion rate
     */
    private function calculateCompletionRate($userId)
    {
        $total = WorkerTask::where('assigned_to', $userId)
            ->whereDate('due_date', '>=', Carbon::now()->subDays(30))
            ->count();
        
        $completed = WorkerTask::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->whereDate('due_date', '>=', Carbon::now()->subDays(30))
            ->count();
        
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
}