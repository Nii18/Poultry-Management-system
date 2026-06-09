<?php
// database/seeders/WorkerTasksSeeder.php

namespace Database\Seeders;

use App\Models\WorkerTask;
use Illuminate\Database\Seeder;

class WorkerTasksSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ── Morning ───────────────────────────────────────────────────────
            [
                'title'       => 'Morning feeding',
                'description' => 'Feed all birds in House A and B',
                'priority'    => 'high',
                'start_time'  => '06:00',
                'end_time'    => '08:00',
                'window'      => 'morning',
            ],
            [
                'title'       => 'Water refill',
                'description' => 'Check and refill waterers in all houses',
                'priority'    => 'high',
                'start_time'  => '08:00',
                'end_time'    => '09:00',
                'window'      => 'morning',
            ],
            [
                'title'       => 'Morning health check',
                'description' => 'Observe birds for any signs of illness or injury',
                'priority'    => 'medium',
                'start_time'  => '09:00',
                'end_time'    => '10:00',
                'window'      => 'morning',
            ],
            // ── Afternoon ─────────────────────────────────────────────────────
            [
                'title'       => 'Egg collection',
                'description' => 'Collect and count eggs from all houses',
                'priority'    => 'high',
                'start_time'  => '13:00',
                'end_time'    => '14:30',
                'window'      => 'afternoon',
            ],
            [
                'title'       => 'House cleaning',
                'description' => 'Clean and disinfect house litter',
                'priority'    => 'low',
                'start_time'  => '14:30',
                'end_time'    => '16:00',
                'window'      => 'afternoon',
            ],
            [
                'title'       => 'Afternoon feeding',
                'description' => 'Second feeding of the day',
                'priority'    => 'medium',
                'start_time'  => '16:00',
                'end_time'    => '17:00',
                'window'      => 'afternoon',
            ],
            // ── Evening ───────────────────────────────────────────────────────
            [
                'title'       => 'Evening health check',
                'description' => 'Final bird observation before closing',
                'priority'    => 'medium',
                'start_time'  => '17:00',
                'end_time'    => '18:00',
                'window'      => 'evening',
            ],
            [
                'title'       => 'Lock up',
                'description' => 'Secure all houses, gates and equipment',
                'priority'    => 'high',
                'start_time'  => '18:00',
                'end_time'    => '18:30',
                'window'      => 'evening',
            ],
        ];

        foreach ($templates as $t) {
            // firstOrCreate keeps re-runs safe — won't duplicate
            WorkerTask::firstOrCreate(
                [
                    'title'        => $t['title'],
                    'is_recurring' => true,
                ],
                array_merge($t, [
                    'is_recurring'      => true,
                    'recurring_pattern' => 'daily',
                    'status'            => 'pending',
                    'due_date'          => today(),   // placeholder — assignments drive real dates
                    'assigned_to'       => 1,         // system placeholder
                    'assigned_by'       => 1,
                ])
            );
        }

        $this->command->info('Worker task templates seeded: ' . count($templates) . ' tasks.');
    }
}