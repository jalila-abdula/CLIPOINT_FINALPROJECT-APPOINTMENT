<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(match (request()->user()->role) {
            User::ROLE_ADMIN => 'portal.admin',
            User::ROLE_RECEPTIONIST => 'portal.receptionist',
            User::ROLE_STAFF => 'portal.staff',
            default => 'login',
        });
    })->name('dashboard');

    Route::get('/admin/portal', [DashboardController::class, 'index'])
        ->middleware('portal:admin')
        ->name('portal.admin');

    Route::get('/receptionist/portal', [DashboardController::class, 'index'])
        ->middleware('portal:receptionist')
        ->name('portal.receptionist');

    Route::get('/staff/portal', [DashboardController::class, 'index'])
        ->middleware('portal:staff')
        ->name('portal.staff');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin,receptionist')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    Route::middleware('role:admin,receptionist,staff')->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::get('appointments-records', [AppointmentController::class, 'showRecords'])->name('appointments.records');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    });

    Route::middleware('role:admin,receptionist,staff')->group(function () {
        Route::get('service-records', [ServiceRecordController::class, 'index'])->name('service-records.index');
        Route::post('service-records', [ServiceRecordController::class, 'store'])->name('service-records.store');
        Route::get('service-records/{serviceRecord}/edit', [ServiceRecordController::class, 'edit'])->name('service-records.edit');
        Route::put('service-records/{serviceRecord}', [ServiceRecordController::class, 'update'])->name('service-records.update');
        Route::delete('service-records/{serviceRecord}', [ServiceRecordController::class, 'destroy'])->name('service-records.destroy');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::patch('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});


require __DIR__.'/auth.php';
