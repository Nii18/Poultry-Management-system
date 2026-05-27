<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\FlockController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\FeedTypeController;
use App\Http\Controllers\FeedDeliveryController;
use App\Http\Controllers\FeedIssuanceController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\BreedingRecordController;
use App\Http\Controllers\OffspringRecordController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\FarmProduceController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::get('/', function () {
    return redirect()->route('login');
});


// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartsData'])->name('dashboard.charts');
    Route::get('/reports/total-animals', [DashboardController::class, 'totalAnimals'])->name('reports.total-animals');
    
    // Species Routes
Route::prefix('species')->name('species.')->middleware(['role:admin,manager'])->group(function () {
    // AJAX routes (must come BEFORE routes with {id} parameters)
    Route::get('/create-form', [SpeciesController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [SpeciesController::class, 'storeSpeciesAjax'])->name('store-ajax');
    Route::get('/{id}/details-json', [SpeciesController::class, 'getDetailsJson'])->name('details-json');
    Route::get('/{id}/edit-data', [SpeciesController::class, 'getEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [SpeciesController::class, 'updateSpeciesAjax'])->name('update-ajax');
    Route::post('/{id}/toggle-status-ajax', [SpeciesController::class, 'toggleStatusAjax'])->name('toggle-status-ajax');
    Route::delete('/{id}/destroy-ajax', [SpeciesController::class, 'destroyAjax'])->name('destroy-ajax')->middleware(['role:admin']);
    
    // Regular routes (keep these for backwards compatibility)
    Route::get('/', [SpeciesController::class, 'index'])->name('index');
    Route::get('/create', [SpeciesController::class, 'create'])->name('create');
    Route::post('/', [SpeciesController::class, 'store'])->name('store');
    Route::get('/{id}', [SpeciesController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SpeciesController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SpeciesController::class, 'update'])->name('update');
    Route::delete('/{id}', [SpeciesController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);
    Route::post('/{id}/toggle-status', [SpeciesController::class, 'toggleStatus'])->name('toggle-status');
});


Route::prefix('produces')->name('produces.')->group(function () {
    // Static/AJAX routes - must come BEFORE {id} routes
    Route::get('/inventory', [FarmProduceController::class, 'inventory'])
        ->name('inventory')
        ->middleware(['role:admin,manager,accountant']);
    
    Route::get('/create-form', [FarmProduceController::class, 'getCreateForm'])
        ->name('create-form')
        ->middleware(['role:admin,manager,worker']);
    
    Route::post('/store-ajax', [FarmProduceController::class, 'storeProduceAjax'])
        ->name('store-ajax')
        ->middleware(['role:admin,manager,worker']);
    
    Route::get('/unit/{type}', [FarmProduceController::class, 'getDefaultUnit'])
        ->name('unit');
    
    Route::get('/stat/{productType}', [FarmProduceController::class, 'getStatCardDetail'])
        ->name('stat-detail');
    
    // Dynamic routes with {id} - place these AFTER static routes
    Route::get('/{id}/details-json', [FarmProduceController::class, 'getDetailsJson'])
        ->name('details-json')
        ->middleware(['role:admin,manager,accountant']);
    
    Route::get('/{id}/edit-data', [FarmProduceController::class, 'getEditData'])
        ->name('edit-data')
        ->middleware(['role:admin,manager']);
    
    Route::put('/{id}/update-ajax', [FarmProduceController::class, 'updateProduceAjax'])
        ->name('update-ajax')
        ->middleware(['role:admin,manager']);
    
    Route::delete('/{id}', [FarmProduceController::class, 'destroy'])
        ->name('destroy')
        ->middleware(['role:admin,manager,worker']);
    
    // Index route LAST
    Route::get('/', [FarmProduceController::class, 'index'])
        ->name('index')
        ->middleware(['role:admin,manager,worker,accountant']);
});


// ==================== WORKER ROUTES ====================
Route::prefix('worker')->name('worker.')->middleware(['auth', 'role:worker'])->group(function () {
    // Tasks
    Route::get('/tasks', [WorkerController::class, 'tasks'])->name('tasks');
    Route::put('/tasks/{id}/status', [WorkerController::class, 'updateTaskStatus'])->name('tasks.update-status');
    
    // Attendance
    Route::get('/attendance', [WorkerController::class, 'attendance'])->name('attendance');
    Route::post('/clock-in', [WorkerController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [WorkerController::class, 'clockOut'])->name('clock-out');
    Route::get('/attendance-data', [WorkerController::class, 'getAttendanceData'])->name('attendance-data');
    
    // Help
    Route::get('/help', [WorkerController::class, 'help'])->name('help');
});

// ==================== MANAGER ROUTES ====================
Route::prefix('manager')->name('manager.')->middleware(['auth', 'role:manager,admin'])->group(function () {
    // Task Management
    Route::get('/tasks', [ManagerController::class, 'manageTasks'])->name('tasks');
    Route::get('/tasks/create', [ManagerController::class, 'createTaskForm'])->name('tasks.create');
    Route::post('/tasks', [ManagerController::class, 'createTask'])->name('tasks.store');
    Route::get('/tasks/{id}/edit', [ManagerController::class, 'editTaskForm'])->name('tasks.edit');
    Route::put('/tasks/{id}', [ManagerController::class, 'editTask'])->name('tasks.update');
    Route::delete('/tasks/{id}', [ManagerController::class, 'deleteTask'])->name('tasks.delete');
    
    // Attendance Reports
    Route::get('/attendance', [ManagerController::class, 'viewAttendance'])->name('attendance');
    Route::get('/attendance/{workerId}/json', [ManagerController::class, 'getWorkerAttendance'])->name('attendance.json');
});
    
    // House Management - Admin and Manager only
    Route::prefix('houses')->name('houses.')->middleware(['role:admin,manager'])->group(function () {
        Route::get('/', [HouseController::class, 'index'])->name('index');
        Route::get('/create', [HouseController::class, 'create'])->name('create');
        Route::post('/', [HouseController::class, 'store'])->name('store');
        Route::get('/{id}', [HouseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [HouseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [HouseController::class, 'update'])->name('update');
        Route::delete('/{id}', [HouseController::class, 'destroy'])->name('destroy');
        Route::get('/reports/occupancy', [HouseController::class, 'occupancyReport'])->name('occupancy-report');

        // AJAX routes for modals
    Route::get('/{id}/details', [HouseController::class, 'getHouseDetails'])->name('details');
    Route::get('/{id}/edit-data', [HouseController::class, 'getHouseEditData'])->name('edit-data');
    });

    // ACCOUNT MANAGEMENT ROUTES - Accessible to All Authenticated Users
Route::middleware(['auth'])->group(function () {
    // Account Management
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('account.avatar.update');
    Route::delete('/account/avatar', [AccountController::class, 'deleteAvatar'])->name('account.avatar.delete');
    
    // Change Password
    Route::get('/account/password', [AccountController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});
    
    // Flock Management - Admin, Manager, and Head Worker
    Route::prefix('flocks')->name('flocks.')->middleware(['role:admin,manager,head_worker'])->group(function () {
        Route::get('/', [FlockController::class, 'index'])->name('index');
        Route::get('/create', [FlockController::class, 'create'])->name('create');
        Route::post('/', [FlockController::class, 'store'])->name('store');
        Route::get('/{id}', [FlockController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FlockController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FlockController::class, 'update'])->name('update');
        Route::delete('/{id}', [FlockController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);
        Route::post('/{id}/close', [FlockController::class, 'close'])->name('close');
        Route::get('/{id}/performance', [FlockController::class, 'performance'])->name('performance');
        // AJAX routes for modals - these should be BEFORE the {id} route
    Route::get('/{id}/details', [FlockController::class, 'getFlockDetails'])->name('details');
    Route::get('/{id}/edit-data', [FlockController::class, 'getFlockEditData'])->name('edit-data');
    });

     
            
    
    // Daily Logs - All workers (including head workers) can create, only head/manager can edit
    Route::prefix('daily-logs')->name('daily-logs.')->group(function () {
        Route::get('/', [DailyLogController::class, 'index'])->name('index');
        Route::get('/create', [DailyLogController::class, 'create'])->name('create')->middleware(['role:admin,manager,worker']);
        Route::post('/', [DailyLogController::class, 'store'])->name('store')->middleware(['role:admin,manager,worker']);
        Route::get('/{id}', [DailyLogController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DailyLogController::class, 'edit'])->name('edit')->middleware(['role:admin,manager,worker']);
        Route::put('/{id}', [DailyLogController::class, 'update'])->name('update')->middleware(['role:admin,manager,worker']);
        Route::delete('/{id}', [DailyLogController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);

        // In the daily-logs route group
Route::get('/{id}/json', [DailyLogController::class, 'getLogJson'])->name('daily-logs.json');
    });
    
    // Feed Types - Admin and Manager only
    Route::prefix('feed-types')->name('feed-types.')->middleware(['role:admin,manager'])->group(function () {
        Route::get('/', [FeedTypeController::class, 'index'])->name('index');
        Route::get('/create', [FeedTypeController::class, 'create'])->name('create');
        Route::post('/', [FeedTypeController::class, 'store'])->name('store');
        Route::get('/{id}', [FeedTypeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FeedTypeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FeedTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeedTypeController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [FeedTypeController::class, 'toggleStatus'])->name('toggle-status');
        // AJAX routes for modals
    Route::get('/{id}/details', [FeedTypeController::class, 'getFeedTypeDetails'])->name('details');
    Route::get('/{id}/edit-data', [FeedTypeController::class, 'getFeedTypeEditData'])->name('edit-data');
    });
    
    // Feed Deliveries - Admin and Manager only
    Route::prefix('feed-deliveries')->name('feed-deliveries.')->middleware(['role:admin,manager'])->group(function () {
        Route::get('/', [FeedDeliveryController::class, 'index'])->name('index');
        Route::get('/create', [FeedDeliveryController::class, 'create'])->name('create');
        Route::post('/', [FeedDeliveryController::class, 'store'])->name('store');
        Route::get('/{id}', [FeedDeliveryController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FeedDeliveryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FeedDeliveryController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeedDeliveryController::class, 'destroy'])->name('destroy');
        Route::get('/alerts/low-stock', [FeedDeliveryController::class, 'lowStock'])->name('low-stock');

        // AJAX routes for modals
    Route::get('/{id}/details', [FeedDeliveryController::class, 'getDeliveryDetails'])->name('details');
    Route::get('/{id}/edit-data', [FeedDeliveryController::class, 'getDeliveryEditData'])->name('edit-data');
    });



    // Feed Issuances - Admin, Manager, Head Worker, and Worker
    Route::prefix('feed-issuances')->name('feed-issuances.')->group(function () {
        Route::get('/', [FeedIssuanceController::class, 'index'])->name('index');
        Route::get('/create', [FeedIssuanceController::class, 'create'])->name('create')->middleware(['role:admin,manager,head_worker,worker']);
        Route::post('/', [FeedIssuanceController::class, 'store'])->name('store')->middleware(['role:admin,manager,head_worker,worker']);
        Route::get('/{id}', [FeedIssuanceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FeedIssuanceController::class, 'edit'])->name('edit')->middleware(['role:admin,manager,head_worker']);
        Route::put('/{id}', [FeedIssuanceController::class, 'update'])->name('update')->middleware(['role:admin,manager,head_worker']);
        Route::delete('/{id}', [FeedIssuanceController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);

         // AJAX routes for modals
    Route::get('/{id}/details', [FeedIssuanceController::class, 'getIssuanceDetails'])->name('details');
    Route::get('/{id}/edit-data', [FeedIssuanceController::class, 'getIssuanceEditData'])->name('edit-data');
    });
    
    // Vaccinations - Admin, Manager, and Veterinarian
Route::prefix('vaccinations')->name('vaccinations.')->middleware(['role:admin,manager,veterinarian'])->group(function () {
    // AJAX routes MUST come BEFORE the {id} routes
    Route::get('/create-form', [VaccinationController::class, 'getCreateForm'])->name('create-form');
    Route::get('/schedule-data', [VaccinationController::class, 'getScheduleData'])->name('schedule-data');
    
    // Standard routes
    Route::get('/', [VaccinationController::class, 'index'])->name('index');
    Route::get('/create', [VaccinationController::class, 'create'])->name('create');
    Route::get('/schedule', [VaccinationController::class, 'schedule'])->name('schedule');
    Route::post('/', [VaccinationController::class, 'store'])->name('store');
    Route::post('/store-ajax', [VaccinationController::class, 'storeVaccination'])->name('store-ajax');
    
    // Routes with {id} parameters - these must come AFTER specific routes
    Route::get('/{id}', [VaccinationController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [VaccinationController::class, 'edit'])->name('edit');
    Route::put('/{id}', [VaccinationController::class, 'update'])->name('update');
    Route::delete('/{id}', [VaccinationController::class, 'destroy'])->name('destroy');
    
    // AJAX routes with {id} parameters
    Route::get('/{id}/details', [VaccinationController::class, 'getVaccinationDetails'])->name('details');
    Route::get('/{id}/edit-data', [VaccinationController::class, 'getVaccinationEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [VaccinationController::class, 'updateVaccination'])->name('update-ajax');
});
  // Treatments - Admin, Manager, and Veterinarian
Route::prefix('treatments')->name('treatments.')->middleware(['role:admin,manager,veterinarian'])->group(function () {
    // AJAX routes MUST come BEFORE the {id} routes
    Route::get('/create-form', [TreatmentController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [TreatmentController::class, 'storeTreatment'])->name('store-ajax');
    
    // Standard routes
    Route::get('/', [TreatmentController::class, 'index'])->name('index');
    Route::get('/create', [TreatmentController::class, 'create'])->name('create');
    Route::post('/', [TreatmentController::class, 'store'])->name('store');
    Route::get('/alerts/withdrawal', [TreatmentController::class, 'withdrawalAlerts'])->name('withdrawal-alerts');
    
    // Routes with {id} parameters - these must come AFTER specific routes
    Route::get('/{id}', [TreatmentController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TreatmentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TreatmentController::class, 'update'])->name('update');
    Route::delete('/{id}', [TreatmentController::class, 'destroy'])->name('destroy');
    
    // AJAX routes with {id} parameters
    Route::get('/{id}/details', [TreatmentController::class, 'getTreatmentDetails'])->name('details');
    Route::get('/{id}/edit-data', [TreatmentController::class, 'getTreatmentEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [TreatmentController::class, 'updateTreatment'])->name('update-ajax');
});
    
    // Health Records - Admin, Manager, and Veterinarian
Route::prefix('health-records')->name('health-records.')->middleware(['role:admin,manager,veterinarian'])->group(function () {
    Route::get('/', [HealthRecordController::class, 'index'])->name('index');


    // Clinical Tools Modal AJAX routes
    Route::get('/drug-formulary-modal', [HealthRecordController::class, 'drugFormularyModal'])->name('drug-formulary-modal');
    Route::get('/disease-guide-modal', [HealthRecordController::class, 'diseaseGuideModal'])->name('disease-guide-modal');
    Route::get('/health-calendar-modal', [HealthRecordController::class, 'healthCalendarModal'])->name('health-calendar-modal');
    
    // Clinical Tools routes
    Route::get('/drug-formulary', [HealthRecordController::class, 'drugFormulary'])->name('drug-formulary');
    Route::get('/disease-guide', [HealthRecordController::class, 'diseaseGuide'])->name('disease-guide');
    Route::get('/health-calendar', [HealthRecordController::class, 'healthCalendar'])->name('health-calendar');
    
    // AJAX routes for modals
    Route::get('/create-form', [HealthRecordController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [HealthRecordController::class, 'storeHealthRecord'])->name('store-ajax');
    Route::get('/{id}/details', [HealthRecordController::class, 'getHealthRecordDetails'])->name('details');
    Route::get('/{id}/edit-data', [HealthRecordController::class, 'getHealthRecordEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [HealthRecordController::class, 'updateHealthRecord'])->name('update-ajax');


    Route::get('/create', [HealthRecordController::class, 'create'])->name('create');
    Route::post('/', [HealthRecordController::class, 'store'])->name('store');
    Route::get('/{id}', [HealthRecordController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [HealthRecordController::class, 'edit'])->name('edit');
    Route::put('/{id}', [HealthRecordController::class, 'update'])->name('update');
    Route::delete('/{id}', [HealthRecordController::class, 'destroy'])->name('destroy');
    Route::get('/alerts/critical', [HealthRecordController::class, 'criticalAlerts'])->name('critical-alerts');

     
    
});
    
   // Breeding Records - Admin, Manager, and Head Worker
Route::prefix('breeding-records')->name('breeding-records.')->middleware(['role:admin,manager,worker'])->group(function () {
    // IMPORTANT: Put specific routes BEFORE routes with parameters
    Route::get('/create-form', [BreedingRecordController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [BreedingRecordController::class, 'storeBreedingRecord'])->name('store-ajax');
    Route::get('/pending', [BreedingRecordController::class, 'pending'])->name('pending');
    
    // Routes with parameters should come AFTER specific routes
    Route::get('/{id}/details-json', [BreedingRecordController::class, 'getDetailsJson'])->name('details-json');
    Route::post('/{id}/record-delivery-ajax', [BreedingRecordController::class, 'recordDeliveryAjax'])->name('record-delivery-ajax');
    Route::get('/{id}/edit', [BreedingRecordController::class, 'edit'])->name('edit');
    Route::put('/{id}', [BreedingRecordController::class, 'update'])->name('update');
    Route::delete('/{id}', [BreedingRecordController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);
    Route::post('/{id}/record-delivery', [BreedingRecordController::class, 'recordDelivery'])->name('record-delivery');
    
    // This should be LAST because it catches any {id} that doesn't match above
    Route::get('/', [BreedingRecordController::class, 'index'])->name('index');
    Route::get('/create', [BreedingRecordController::class, 'create'])->name('create');
    Route::post('/', [BreedingRecordController::class, 'store'])->name('store');
    Route::get('/{id}', [BreedingRecordController::class, 'show'])->name('show');
});
    
    // Offspring Records - Admin, Manager, and Head Worker
    Route::prefix('offspring-records')->name('offspring-records.')->middleware(['role:admin,manager,worker'])->group(function () {
        Route::get('/create', [OffspringRecordController::class, 'create'])->name('create');
        Route::post('/', [OffspringRecordController::class, 'store'])->name('store');
        Route::get('/{id}', [OffspringRecordController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OffspringRecordController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OffspringRecordController::class, 'update'])->name('update');
        Route::delete('/{id}', [OffspringRecordController::class, 'destroy'])->name('destroy')->middleware(['role:admin']);
    });
    
    // Expenses Routes
Route::prefix('expenses')->name('expenses.')->middleware(['role:admin,manager,accountant'])->group(function () {
    // AJAX routes (must come BEFORE routes with {id} parameters)
    Route::get('/create-form', [ExpenseController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [ExpenseController::class, 'storeExpenseAjax'])->name('store-ajax');
    Route::get('/{id}/details-json', [ExpenseController::class, 'getDetailsJson'])->name('details-json');
    Route::get('/{id}/edit-data', [ExpenseController::class, 'getEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [ExpenseController::class, 'updateExpenseAjax'])->name('update-ajax');
    
    // Regular routes
    Route::get('/', [ExpenseController::class, 'index'])->name('index');
    Route::get('/by-category', [ExpenseController::class, 'byCategory'])->name('by-category');
    Route::get('/create', [ExpenseController::class, 'create'])->name('create');
    Route::post('/', [ExpenseController::class, 'store'])->name('store');
    Route::get('/{id}', [ExpenseController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ExpenseController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ExpenseController::class, 'update'])->name('update');
    Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('destroy');
});



// Sales Routes
Route::prefix('sales')->name('sales.')->middleware(['role:admin,manager,accountant'])->group(function () {
    // AJAX routes (must come BEFORE routes with {id} parameters)
    Route::get('/create-form', [SaleController::class, 'getCreateForm'])->name('create-form');
    Route::post('/store-ajax', [SaleController::class, 'storeSaleAjax'])->name('store-ajax');
    Route::get('/{id}/details-json', [SaleController::class, 'getDetailsJson'])->name('details-json');
    Route::get('/{id}/edit-data', [SaleController::class, 'getEditData'])->name('edit-data');
    Route::put('/{id}/update-ajax', [SaleController::class, 'updateSaleAjax'])->name('update-ajax');
    
    // Regular routes
    Route::get('/', [SaleController::class, 'index'])->name('index');
    Route::get('/by-product', [SaleController::class, 'byProductType'])->name('by-product');
    Route::delete('/{id}', [SaleController::class, 'destroy'])->name('destroy');
});
    
    // Reports - Admin, Manager, Accountant, and Veterinarian
    Route::prefix('reports')->name('reports.')->middleware(['role:admin,manager,accountant,veterinarian'])->group(function () {

         
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/health', [ReportController::class, 'health'])->name('health');


       // Add this to your routes/web.php inside the auth group
       Route::get('/api/profit-loss', [ReportController::class, 'getProfitLossData'])->name('api.profit-loss');
    });

    // ADMIN ROUTES
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    
    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    
});
    // Notifications - All authenticated users
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread/count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/{id}/json', [NotificationController::class, 'getNotificationJson'])->name('json');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    Route::post('/{id}/read', [NotificationController::class, 'markAsReadAjax'])->name('mark-read');
    Route::post('/{id}/mark-read-ajax', [NotificationController::class, 'markAsReadAjax'])->name('mark-read-ajax');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/clear/all', [NotificationController::class, 'clearAll'])->name('clear-all');
});
    // User Management - Admin only
    Route::prefix('users')->name('users.')->middleware(['role:admin'])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/password', [UserController::class, 'updatePassword'])->name('update-password');
    });

    // USER SWITCHING ROUTES -  Admin Only
Route::middleware(['auth'])->group(function () {
    Route::get('/switch-user', [UserController::class, 'switchUser'])->name('user.switch');
    Route::post('/switch-to-user/{user}', [UserController::class, 'switchToUser'])->name('user.switch.to');
    Route::post('/switch-back', [UserController::class, 'switchBack'])->name('user.switch.back');
});


// routes/web.php

// Audit Logs (Admin & Manager only)
Route::middleware(['auth'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    
   
});


// Search Routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search', [SearchController::class, 'apiSearch'])->name('api.search');

// Notifications API
Route::get('/api/notifications', [NotificationController::class, 'apiNotifications'])->name('api.notifications');

    
    // Settings - Admin only
    Route::prefix('settings')->name('settings.')->middleware(['role:admin'])->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('update-general');
        Route::post('/alerts', [SettingsController::class, 'updateAlerts'])->name('update-alerts');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
        Route::get('/backup', [SettingsController::class, 'backupDatabase'])->name('backup');
    });
    
    // Analytics - Admin and Manager only
    Route::prefix('analytics')->name('analytics.')->middleware(['role:admin,manager'])->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
    });
    
    // Quick Actions (AJAX endpoints)
    Route::prefix('quick-actions')->name('quick-actions.')->group(function () {
        Route::post('/daily-log', [DailyLogController::class, 'store'])->name('daily-log')->middleware(['role:admin,manager,head_worker,worker']);
        Route::post('/feed-issuance', [FeedIssuanceController::class, 'store'])->name('feed-issuance')->middleware(['role:admin,manager,head_worker,worker']);
        Route::post('/expense', [ExpenseController::class, 'store'])->name('expense')->middleware(['role:admin,manager,accountant']);
    });
    

    // Profile Routes - All authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';