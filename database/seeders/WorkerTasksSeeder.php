<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerTask;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WorkerTasksSeeder extends Seeder
{
    public function run()
    {
        $workers = User::where('role', 'worker')->get();
        $today = Carbon::today();
        
        $defaultTasks = [
            [
                'title' => 'Morning Feeding',
                'description' => 'Feed all birds in House A and B',
                'priority' => 'high',
                'start_time' => '06:00:00',
                'end_time' => '08:00:00'
            ],
            [
                'title' => 'Water Refill',
                'description' => 'Check and refill waterers in all houses',
                'priority' => 'high',
                'start_time' => '08:00:00',
                'end_time' => '09:00:00'
            ],
            [
                'title' => 'Health Check',
                'description' => 'Observe birds for any signs of illness',
                'priority' => 'medium',
                'start_time' => '09:00:00',
                'end_time' => '10:00:00'
            ],
            [
                'title' => 'House Cleaning',
                'description' => 'Clean House C litter',
                'priority' => 'low',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00'
            ],
            [
                'title' => 'Afternoon Feeding',
                'description' => 'Second feeding of the day',
                'priority' => 'medium',
                'start_time' => '16:00:00',
                'end_time' => '17:00:00'
            ],
            [
                'title' => 'Evening Health Check',
                'description' => 'Final observation before closing',
                'priority' => 'medium',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00'
            ]
        ];
        
        foreach ($workers as $worker) {
            foreach ($defaultTasks as $task) {
                // Check if task already exists for today
                $exists = WorkerTask::where('assigned_to', $worker->id)
                    ->whereDate('due_date', $today)
                    ->where('title', $task['title'])
                    ->exists();
                
                if (!$exists) {
                    WorkerTask::create([
                        'title' => $task['title'],
                        'description' => $task['description'],
                        'priority' => $task['priority'],
                        'due_date' => $today,
                        'start_time' => $task['start_time'],
                        'end_time' => $task['end_time'],
                        'assigned_to' => $worker->id,
                        'assigned_by' => 1, // Assuming admin user with ID 1 exists
                        'status' => 'pending'
                    ]);
                }
            }
        }
        
        $this->command->info('Worker tasks seeded successfully!');
    }
}