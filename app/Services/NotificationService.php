<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\WorkerTaskAssignment;
use App\Models\User;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\FeedDelivery;
use App\Models\Treatment;
use Carbon\Carbon;

class NotificationService
{
    // ══════════════════════════════════════════════════════════════
    // ROLE HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Notify all users of given roles.
     * Skips if an identical title was already sent to that user today
     * (prevents duplicates from multiple triggers of the same event).
     */
    private function notifyRoles(array $roles, string $type, string $severity, string $title, string $message, ?int $flockId = null, ?int $createdBy = null): void
    {
        $users = User::whereIn('role', $roles)->where('is_active', true)->get();

        foreach ($users as $user) {
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('title', $title)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadySent) continue;

            Notification::create([
                'user_id'    => $user->id,
                'flock_id'   => $flockId,
                'type'       => $type,
                'severity'   => $severity,
                'title'      => $title,
                'message'    => $message,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        }
    }

    /**
     * Notify a single specific user by ID.
     */
    private function notifyUser(int $userId, string $type, string $severity, string $title, string $message, ?int $flockId = null, ?int $createdBy = null): void
    {
        Notification::create([
            'user_id'    => $userId,
            'flock_id'   => $flockId,
            'type'       => $type,
            'severity'   => $severity,
            'title'      => $title,
            'message'    => $message,
            'created_by' => $createdBy,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DAILY LOG ALERTS  (DailyLogController::checkForAlerts)
    // ══════════════════════════════════════════════════════════════

    /**
     * Replace the inline Notification::create() calls in DailyLogController.
     * Call this instead of the three separate creates in checkForAlerts().
     */
    public function notifyHighMortality(int $flockId, string $flockNumber, float $rate, int $mortalityCount, int $cullingCount): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'high_mortality',
            'critical',
            "High Mortality Detected — Flock #{$flockNumber}",
            "Mortality rate of " . round($rate, 2) . "% detected in flock {$flockNumber}. "
                . "Deaths: {$mortalityCount}, Culled: {$cullingCount}.",
            $flockId
        );
    }

    public function notifyHighTemperature(int $flockId, string $flockNumber, string $houseName, float $temp, int $optimal): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'high_temperature',
            'warning',
            "Temperature Alert — Flock #{$flockNumber}",
            "High temperature of {$temp}°C recorded in {$houseName} (optimal: {$optimal}°C). Check ventilation.",
            $flockId
        );
    }

    public function notifyHighAmmonia(int $flockId, string $flockNumber, float $ppm): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'high_ammonia',
            'warning',
            "High Ammonia Alert — Flock #{$flockNumber}",
            "Ammonia level of {$ppm}ppm detected in flock {$flockNumber}. Risk of respiratory issues.",
            $flockId
        );
    }

    // ══════════════════════════════════════════════════════════════
    // EXPENSE NOTIFICATIONS  (ExpenseController)
    // ══════════════════════════════════════════════════════════════

    public function notifyExpenseRecorded(float $amount, string $category, string $description, string $recordedBy): void
    {
        $formatted = number_format($amount, 2);

        $this->notifyRoles(
            ['admin', 'manager', 'accountant'],
            'expense',
            'info',
            "New Expense Recorded — {$category}",
            "{$recordedBy} recorded a {$category} expense of GHS {$formatted}: \"{$description}\"."
        );
    }

    // ══════════════════════════════════════════════════════════════
    // SALE NOTIFICATIONS  (SaleController)
    // ══════════════════════════════════════════════════════════════

    public function notifySaleRecorded(string $productType, float $quantity, float $totalAmount, string $recordedBy): void
    {
        $formatted   = number_format($totalAmount, 2);
        $productLabel = ucwords(str_replace('_', ' ', $productType));

        $this->notifyRoles(
            ['admin', 'manager', 'accountant'],
            'financial',
            'info',
            "New Sale Recorded — {$productLabel}",
            "{$recordedBy} recorded a sale of {$quantity} {$productLabel} totalling GHS {$formatted}."
        );
    }

    // ══════════════════════════════════════════════════════════════
    // FEED DELIVERY / LOW STOCK  (FeedDeliveryController)
    // ══════════════════════════════════════════════════════════════

    public function notifyLowFeedStock(string $feedTypeName, float $remainingKg): void
    {
        $this->notifyRoles(
            ['admin', 'manager'],
            'operational',
            'warning',
            "Low Feed Stock — {$feedTypeName}",
            "Feed stock for {$feedTypeName} is critically low at {$remainingKg}kg remaining. Please reorder soon."
        );
    }

    /**
     * Call this after every FeedDelivery store/update to check all feed types.
     * Fires once per feed type per day when stock drops below 500 kg.
     */
    public function checkAndNotifyLowStock(): void
    {
        $lowStock = FeedDelivery::with('feedType')
            ->where('remaining_quantity_kg', '>', 0)
            ->where('remaining_quantity_kg', '<', 500)
            ->where('expiry_date', '>', now())
            ->get()
            ->groupBy('feed_type_id');

        foreach ($lowStock as $feedTypeId => $deliveries) {
            $totalRemaining = $deliveries->sum('remaining_quantity_kg');
            $feedTypeName   = $deliveries->first()->feedType->name ?? 'Unknown';

            $this->notifyLowFeedStock($feedTypeName, $totalRemaining);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // TREATMENT NOTIFICATIONS  (TreatmentController)
    // ══════════════════════════════════════════════════════════════

    public function notifyTreatmentStarted(int $flockId, string $flockNumber, string $productName, string $diagnosis, ?string $withdrawalEndDate): void
    {
        $withdrawalText = $withdrawalEndDate
            ? " Withdrawal period ends: {$withdrawalEndDate}."
            : '';

        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'treatment',
            'info',
            "Treatment Started — Flock #{$flockNumber}",
            "Treatment \"{$productName}\" for {$diagnosis} started on flock {$flockNumber}.{$withdrawalText}",
            $flockId
        );
    }

    public function notifyWithdrawalPeriodActive(int $flockId, string $flockNumber, string $productName, string $withdrawalEndDate): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'treatment',
            'warning',
            "Withdrawal Period Active — Flock #{$flockNumber}",
            "Flock {$flockNumber} is under withdrawal for \"{$productName}\". Do NOT sell until {$withdrawalEndDate}.",
            $flockId
        );
    }

    /**
     * Called by a scheduled command (or manually) to warn about
     * withdrawals ending within the next 3 days.
     */
    public function notifyWithdrawalEndingSoon(int $flockId, string $flockNumber, string $productName, string $withdrawalEndDate): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'treatment',
            'warning',
            "Withdrawal Ending Soon — Flock #{$flockNumber}",
            "The withdrawal period for \"{$productName}\" on flock {$flockNumber} ends on {$withdrawalEndDate}.",
            $flockId
        );
    }

    // ══════════════════════════════════════════════════════════════
    // HEALTH RECORD NOTIFICATIONS  (HealthRecordController)
    // ══════════════════════════════════════════════════════════════

    public function notifyHealthRecord(int $flockId, string $flockNumber, string $recordType, string $severity, ?int $affectedCount, ?string $condition): void
    {
        if ($severity !== 'critical' && $severity !== 'warning') {
            return; // Only notify for warning and critical health records
        }

        $affectedText = $affectedCount ? " Affected: {$affectedCount} animals." : '';
        $conditionText = $condition ? " Condition: {$condition}." : '';

        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'health',
            $severity,
            ucfirst($severity) . " Health Record — Flock #{$flockNumber}",
            "A {$severity} {$recordType} record was added for flock {$flockNumber}.{$conditionText}{$affectedText}",
            $flockId
        );
    }

    // ══════════════════════════════════════════════════════════════
    // VACCINATION NOTIFICATIONS  (VaccinationController)
    // ══════════════════════════════════════════════════════════════

    public function notifyVaccinationRecorded(int $flockId, string $flockNumber, string $vaccineName, string $diseaseTarget, int $birdsVaccinated, string $administeredBy): void
    {
        $this->notifyRoles(
            ['admin', 'veterinarian'],
            'vaccination',
            'info',
            "Vaccination Recorded — Flock #{$flockNumber}",
            "{$administeredBy} recorded {$vaccineName} (targeting {$diseaseTarget}) for {$birdsVaccinated} birds in flock {$flockNumber}.",
            $flockId
        );
    }

    // ══════════════════════════════════════════════════════════════
    // BREEDING NOTIFICATIONS  (BreedingRecordController)
    // ══════════════════════════════════════════════════════════════

    public function notifyBreedingDelivery(int $flockId, string $flockNumber, int $offspringCount, int $stillbornCount): void
    {
        $this->notifyRoles(
            ['admin', 'manager', 'veterinarian'],
            'health',
            'info',
            "Breeding Delivery Recorded — Flock #{$flockNumber}",
            "Delivery recorded for flock {$flockNumber}. Offspring: {$offspringCount}, Stillborn: {$stillbornCount}.",
            $flockId
        );
    }

    // ══════════════════════════════════════════════════════════════
    // WORKER TASK NOTIFICATIONS  (ManagerController + Login)
    // ══════════════════════════════════════════════════════════════

    public function notifyWorkerNewTaskAssigned(int $workerId, string $taskTitle, string $dueDate, ?string $window): void
    {
        $windowLabel  = $window ? ucfirst($window) . ' window — ' : '';
        $formattedDate = Carbon::parse($dueDate)->format('M d, Y');

        $this->notifyUser(
            $workerId,
            'task',
            'info',
            "New Task Assigned: {$taskTitle}",
            "A new task has been assigned to you: \"{$taskTitle}\". {$windowLabel}Due: {$formattedDate}.",
            null,
            auth()->id()
        );
    }

    public function notifyWorkerWindowTasks(User $worker, string $window): void
    {
        $today = Carbon::today()->toDateString();

        $alreadySent = Notification::where('user_id', $worker->id)
            ->where('type', 'task')
            ->where('title', 'like', "%{$window}%")
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadySent) return;

        $assignments = WorkerTaskAssignment::with('task')
            ->where('assigned_to', $worker->id)
            ->whereDate('assignment_date', $today)
            ->whereHas('task', fn($q) => $q->where('window', $window))
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        if ($assignments->isEmpty()) return;

        $count      = $assignments->count();
        $taskTitles = $assignments->map(fn($a) => '• ' . $a->task->title)->join("\n");

        $this->notifyUser(
            $worker->id,
            'task',
            'warning',
            ucfirst($window) . " Tasks Due — {$count} pending",
            "You have {$count} " . ucfirst($window) . " task(s) to complete today:\n{$taskTitles}"
        );
    }

    public function notifyWorkerPendingTasksOnLogin(User $worker): void
    {
        $today = Carbon::today()->toDateString();

        $assignments = WorkerTaskAssignment::with('task')
            ->where('assigned_to', $worker->id)
            ->whereDate('assignment_date', $today)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get()
            ->filter(function ($assignment) use ($worker) {
                return !Notification::where('user_id', $worker->id)
                    ->where('type', 'task')
                    ->where('title', 'like', '%' . $assignment->task->title . '%')
                    ->exists();
            });

        foreach ($assignments as $assignment) {
            $task          = $assignment->task;
            $windowLabel   = $task->window ? ucfirst($task->window) . ' — ' : '';
            $formattedDate = Carbon::parse($task->due_date)->format('M d, Y');

            $this->notifyUser(
                $worker->id,
                'task',
                'info',
                "Pending Task: {$task->title}",
                "Reminder: \"{$task->title}\" is pending. {$windowLabel}Due: {$formattedDate}."
            );
        }
    }

    // ══════════════════════════════════════════════════════════════
    // PERIODIC / SCHEDULED SUMMARIES
    // ══════════════════════════════════════════════════════════════

    /**
     * Weekly financial summary — call from a scheduled command every Monday.
     */
    public function notifyWeeklyFinancialSummary(): void
    {
        $start   = Carbon::now()->subWeek()->startOfWeek();
        $end     = Carbon::now()->subWeek()->endOfWeek();
        $revenue = Sale::whereBetween('sale_date', [$start, $end])->sum('total_amount');
        $expenses = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $profit  = $revenue - $expenses;
        $week    = $start->format('d M') . ' – ' . $end->format('d M Y');

        $this->notifyRoles(
            ['admin', 'manager', 'accountant'],
            'financial',
            $profit >= 0 ? 'info' : 'warning',
            "Weekly Financial Summary — {$week}",
            "Revenue: GHS " . number_format($revenue, 2)
                . " | Expenses: GHS " . number_format($expenses, 2)
                . " | Net: GHS " . number_format($profit, 2) . "."
        );
    }

    /**
     * Monthly financial summary — call from a scheduled command on the 1st.
     */
    public function notifyMonthlyFinancialSummary(): void
    {
        $month    = Carbon::now()->subMonth();
        $start    = $month->copy()->startOfMonth();
        $end      = $month->copy()->endOfMonth();
        $revenue  = Sale::whereBetween('sale_date', [$start, $end])->sum('total_amount');
        $expenses = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $profit   = $revenue - $expenses;

        $this->notifyRoles(
            ['admin', 'manager', 'accountant'],
            'financial',
            $profit >= 0 ? 'info' : 'warning',
            "Monthly Summary — {$month->format('F Y')}",
            "Revenue: GHS " . number_format($revenue, 2)
                . " | Expenses: GHS " . number_format($expenses, 2)
                . " | Net Profit: GHS " . number_format($profit, 2) . "."
        );
    }

    // ══════════════════════════════════════════════════════════════
    // UTILITY
    // ══════════════════════════════════════════════════════════════

    public function currentWindow(): ?string
    {
        $hour = Carbon::now()->hour;
        if ($hour >= 6  && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 22) return 'evening';
        return null;
    }
}