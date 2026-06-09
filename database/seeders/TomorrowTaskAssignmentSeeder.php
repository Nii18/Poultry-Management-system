<?php
// database/seeders/TomorrowTaskAssignmentSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerTask;
use App\Models\WorkerTaskAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TomorrowTaskAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $tomorrow = Carbon::tomorrow();
        $worker   = User::where('role', 'worker')->first();

        if (!$worker) {
            $this->command->error('No worker user found.');
            return;
        }

        // Wipe tomorrow's assignments for a clean slate
        WorkerTaskAssignment::where('assigned_to', $worker->id)
            ->whereDate('assignment_date', $tomorrow)
            ->delete();

        $tasksByWindow = WorkerTask::where('is_recurring', true)
            ->get()
            ->groupBy('window');

        // ── Morning: all pending (you'll check them tomorrow morning) ─────
        foreach ($tasksByWindow->get('morning', collect()) as $task) {
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $tomorrow,
                'status'          => 'pending',
                'is_completed'    => false,
                'completed_at'    => null,
            ]);
            $this->command->line("  ☐ PENDING [morning] {$task->title}");
        }

        // ── Afternoon: all pending ────────────────────────────────────────
        foreach ($tasksByWindow->get('afternoon', collect()) as $task) {
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $tomorrow,
                'status'          => 'pending',
                'is_completed'    => false,
                'completed_at'    => null,
            ]);
            $this->command->line("  ☐ PENDING [afternoon] {$task->title}");
        }

        // ── Evening: all pending ──────────────────────────────────────────
        foreach ($tasksByWindow->get('evening', collect()) as $task) {
            WorkerTaskAssignment::create([
                'task_id'         => $task->id,
                'assigned_to'     => $worker->id,
                'assignment_date' => $tomorrow,
                'status'          => 'pending',
                'is_completed'    => false,
                'completed_at'    => null,
            ]);
            $this->command->line("  ☐ PENDING [evening] {$task->title}");
        }

        $this->command->newLine();
        $this->command->info("✓ Seeded " . ($tasksByWindow->flatten()->count()) . " tasks for tomorrow ({$tomorrow->format('D, M d Y')})");
        $this->command->info("Tomorrow morning, log in as: {$worker->email}");
        $this->command->info("Morning window opens at 06:00 — checkboxes will be live!");
    }
}