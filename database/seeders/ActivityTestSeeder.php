<?php
// database/seeders/ActivityTestSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ActivityTestSeeder extends Seeder
{
    public function run(): void
    {
        $workers = [
            [
                'name'             => 'John Mensah',
                'email'            => 'john.mensah@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now(),
                'last_login_at'    => now(),
                'last_activity_at' => now(),
                'label'            => '🟢 Online Now',
            ],
            [
                'name'             => 'Ama Owusu',
                'email'            => 'ama.owusu@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now()->subMinutes(20),
                'last_login_at'    => now()->subMinutes(20),
                'last_activity_at' => now()->subMinutes(20),
                'label'            => '🟡 Active Recently',
            ],
            [
                'name'             => 'Kofi Asante',
                'email'            => 'kofi.asante@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now()->subHours(5),
                'last_login_at'    => now()->subHours(5),
                'last_activity_at' => now()->subHours(5),
                'label'            => '⚫ Offline (active today)',
            ],
            [
                'name'             => 'Abena Boateng',
                'email'            => 'abena.boateng@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now()->subDays(3),
                'last_login_at'    => now()->subDays(3),
                'last_activity_at' => now()->subDays(3),
                'label'            => '⚫ Inactive 3 days',
            ],
            [
                'name'             => 'Kwame Darko',
                'email'            => 'kwame.darko@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now()->subDays(10),
                'last_login_at'    => now()->subDays(10),
                'last_activity_at' => now()->subDays(10),
                'label'            => '⚫ Inactive 10 days',
            ],
            [
                'name'             => 'Efua Appiah',
                'email'            => 'efua.appiah@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => now()->subDays(20),
                'last_login_at'    => now()->subDays(20),
                'last_activity_at' => now()->subDays(20),
                'label'            => '⚫ Inactive 20 days',
            ],
            [
                'name'             => 'Yaw Frimpong',
                'email'            => 'yaw.frimpong@livestock.com',
                'role'             => 'worker',
                'last_seen_at'     => null,
                'last_login_at'    => null,
                'last_activity_at' => null,
                'label'            => '⚫ Never logged in',
            ],
        ];

        foreach ($workers as $data) {
            $label = $data['label'];
            unset($data['label']);

            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'   => Hash::make('password'),
                    'phone'      => '02' . rand(10000000, 99999999),
                    'farm_name'  => 'Main Farm',
                    'is_active'  => true,
                ])
            );

            $this->command->info("✓ {$data['name']} → {$label}");
        }

        $this->command->newLine();
        $this->command->info('Done! You should now see:');
        $this->command->info('  Active Workers   → John, Ama, Kofi');
        $this->command->info('  Inactive Workers → Abena, Kwame, Efua, Yaw');
    }
}