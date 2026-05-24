<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@livestock.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '1234567890',
                'farm_name' => 'Main Farm',
                'is_active' => true,
            ]
        );

        // Farm Manager
        User::updateOrCreate(
            ['email' => 'manager@livestock.com'],
            [
                'name' => 'Farm Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'phone' => '1234567891',
                'farm_name' => 'Main Farm',
                'is_active' => true,
            ]
        );

        // Farm Worker
        User::updateOrCreate(
            ['email' => 'worker@livestock.com'],
            [
                'name' => 'Farm Worker',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'phone' => '1234567893',
                'farm_name' => 'Main Farm',
                'is_active' => true,
            ]
        );

        // Veterinarian
        User::updateOrCreate(
            ['email' => 'vet@livestock.com'],
            [
                'name' => 'Dr. Smith',
                'password' => Hash::make('password'),
                'role' => 'veterinarian',
                'phone' => '1234567894',
                'farm_name' => 'Main Farm',
                'is_active' => true,
            ]
        );

        // Accountant
        User::updateOrCreate(
            ['email' => 'accountant@livestock.com'],
            [
                'name' => 'Accountant',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'phone' => '1234567895',
                'farm_name' => 'Main Farm',
                'is_active' => true,
            ]
        );

        $this->command->info('Users seeded successfully!');
    }
}