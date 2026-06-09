<?php
// database/seeders/TodayTaskAssignmentSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TodayTaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $today  = Carbon::today();
        $worker = User::where('role', 'worker')->first();

        if (!$worker) {
            $this->command->error('No worker user found.');
            return;
        }

        // Wipe ALL today's assignments across all users for a clean slate
        WorkerTaskAssignment::whereDate('assignment_date', $today)->delete();

        $tasksByWindow = WorkerTask::where('is_recurring', true)
            ->get()
            ->groupBy('window');

        $results = [];

        // ── Morning (past, mix of completed + missed) ─────────────────────
        foreach ($tasksByWindow->get('morning', collect()) as $index => $task) {
            $isCompleted = $index === 0; // First morning task completed
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $today,
                'status'          => $isCompleted ? 'completed' : 'missed',
                'is_completed'    => $isCompleted,
                'completed_at'    => $isCompleted
                    ? $today->copy()->setTime(7, 30, 0)   // 7:30 AM — within morning window
                    : null,
            ]);
            $results[] = $isCompleted
                ? "  ✓ COMPLETED [morning] {$task->title}"
                : "  ✗ MISSED    [morning] {$task->title}";
        }

        // ── Afternoon (past, most completed) ──────────────────────────────
        foreach ($tasksByWindow->get('afternoon', collect()) as $index => $task) {
            $isCompleted = $index < 2; // First 2 afternoon tasks completed
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $today,
                'status'          => $isCompleted ? 'completed' : 'missed',
                'is_completed'    => $isCompleted,
                'completed_at'    => $isCompleted
                    ? $today->copy()->setTime(rand(13, 15), rand(0, 59), 0) // 1-3 PM
                    : null,
            ]);
            $results[] = $isCompleted
                ? "  ✓ COMPLETED [afternoon] {$task->title}"
                : "  ✗ MISSED    [afternoon] {$task->title}";
        }

        // ── Evening (live window, all pending = checkable) ────────────────
        foreach ($tasksByWindow->get('evening', collect()) as $task) {
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $today,
                'status'          => 'pending',
                'is_completed'    => false,
                'completed_at'    => null,
            ]);
            $results[] = "  ☐ PENDING   [evening] {$task->title}";
        }

        foreach ($results as $line) {
            $this->command->line($line);
        }

        $this->command->newLine();
        $this->command->warn('IMPORTANT: generateForWorker() runs on page load.');
        $this->command->warn('It uses firstOrCreate — will NOT overwrite these records.');
        $this->command->warn('But markMissedForWorker() must have the window-based fix applied!');
    }
}