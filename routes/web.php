<?php

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\OwnerAdminController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerSettingController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Auth Routes (Guest Only)
// If they are already logged in, they will be redirected to the dashboard automatically
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
    
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        $attendanceUrl = URL::temporarySignedRoute(
            'attendance.scan', 
            now()->addHours(8), 
            ['action' => 'process']
        );
        
        return view('employee.dashboard', compact('attendanceUrl'));
    })->name('dashboard');

    // Route Group for Owner Operations
    Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/manage-admins', [OwnerAdminController::class, 'index'])->name('admins.index');
        Route::post('/manage-admins/store', [OwnerAdminController::class, 'store'])->name('admins.store');
        Route::delete('/manage-admins/{id}', [OwnerAdminController::class, 'destroy'])->name('admins.destroy');
        
        Route::get('/settings', [OwnerSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/update', [OwnerSettingController::class, 'update'])->name('settings.update');
    });

    Route::post('/attendance/izin', [AttendanceController::class, 'storeIzin'])->name('attendance.store-izin');

    Route::middleware(['can:access-admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AttendanceController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('admin.employees.index');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('admin.employees.create');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('admin.employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('admin.employees.update');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('admin.employees.store');

        Route::get('/leaves-approval', [AttendanceController::class, 'leavesIndex'])->name('admin.leaves.index');
        Route::post('/leaves-approval/{id}/approve', [AttendanceController::class, 'approveLeave'])->name('admin.leaves.approve');
        Route::post('/leaves-approval/{id}/reject', [AttendanceController::class, 'rejectLeave'])->name('admin.leaves.reject');
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::get('/admin/generate-qr', [AttendanceController::class, 'showScanner'])->middleware('can:admin-only');
Route::get('/attendance/scan/{action}', [AttendanceController::class, 'processScan'])
        ->name('attendance.scan')
        ->middleware('signed');