<?php
// database/seeders/ExpenseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Flock;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run()
    {
        $flock = Flock::where('flock_number', '2024-CH-H01-001')->first();

        // Feed Expenses
        Expense::create([
            'flock_id' => $flock->id,
            'category' => 'feed',
            'description' => 'Chicken Starter Feed',
            'amount' => 4250.00,
            'expense_date' => Carbon::now()->subDays(30),
            'payment_method' => 'bank_transfer',
            'vendor_name' => 'AgriFeed Supplies Ltd',
            'notes' => 'First batch of starter feed',
            'created_by' => 1,
        ]);

        Expense::create([
            'flock_id' => $flock->id,
            'category' => 'feed',
            'description' => 'Chicken Grower Feed',
            'amount' => 6560.00,
            'expense_date' => Carbon::now()->subDays(20),
            'payment_method' => 'bank_transfer',
            'vendor_name' => 'AgriFeed Supplies Ltd',
            'notes' => 'Grower feed for broilers',
            'created_by' => 1,
        ]);

        // Veterinary Expenses
        Expense::create([
            'flock_id' => $flock->id,
            'category' => 'veterinary',
            'description' => 'Vaccination Services',
            'amount' => 500.00,
            'expense_date' => Carbon::now()->subDays(18),
            'payment_method' => 'cash',
            'vendor_name' => 'Vet Services Ltd',
            'notes' => 'Vaccination for the flock',
            'created_by' => 1,
        ]);

        Expense::create([
            'flock_id' => $flock->id,
            'category' => 'medication',
            'description' => 'Antibiotics Treatment',
            'amount' => 250.00,
            'expense_date' => Carbon::now()->subDays(5),
            'payment_method' => 'cash',
            'vendor_name' => 'PharmaVet',
            'notes' => 'Treatment for coccidiosis',
            'created_by' => 1,
        ]);

        // Labor Expense
        Expense::create([
            'category' => 'labor',
            'description' => 'Farm Workers Monthly Salary',
            'amount' => 5000.00,
            'expense_date' => Carbon::now()->subDays(5),
            'payment_method' => 'bank_transfer',
            'vendor_name' => null,
            'notes' => 'Monthly payroll',
            'created_by' => 1,
        ]);

        // Utilities Expense
        Expense::create([
            'category' => 'utilities',
            'description' => 'Electricity Bill',
            'amount' => 800.00,
            'expense_date' => Carbon::now()->subDays(10),
            'payment_method' => 'bank_transfer',
            'vendor_name' => 'Power Company',
            'notes' => 'Monthly electricity',
            'created_by' => 1,
        ]);

        // Equipment Expense
        Expense::create([
            'category' => 'equipment',
            'description' => 'New Feeders and Drinkers',
            'amount' => 1200.00,
            'expense_date' => Carbon::now()->subDays(25),
            'payment_method' => 'bank_transfer',
            'vendor_name' => 'Farm Equipment Ltd',
            'notes' => 'Replacement equipment',
            'created_by' => 1,
        ]);

        $this->command->info('Expenses seeded successfully!');
    }
}