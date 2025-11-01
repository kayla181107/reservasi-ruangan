<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReservationLogController;

// AUTH ROUTES (Public)
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

Route::middleware('auth:api')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.detail');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('auth.logout');


    // BISA DIAKSES ADMIN & KARYAWAN

    Route::middleware('role:admin|karyawan')->group(function () {

        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.list');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('rooms.detail');

        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('fixed-schedules.list');
        Route::get('fixed-schedules/{schedule}', [FixedScheduleController::class, 'show'])->name('fixed-schedules.detail');

        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.list');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.detail');
    });
 
    // ADMIN 
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        Route::post('rooms', [RoomController::class, 'store'])->name('rooms.create');
        Route::put('rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.delete');

        Route::post('fixed-schedules', [FixedScheduleController::class, 'store'])->name('fixed-schedules.create');
        Route::put('fixed-schedules/{schedule}', [FixedScheduleController::class, 'update'])->name('fixed-schedules.update');
        Route::delete('fixed-schedules/{schedule}', [FixedScheduleController::class, 'destroy'])->name('fixed-schedules.delete');

        Route::get('users', [UserController::class, 'index'])->name('users.list');
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.detail');
        Route::post('users', [UserController::class, 'store'])->name('users.create');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.delete');

         
        Route::get('reservations/export/excel', [ReservationController::class, 'exportExcel'])
            ->name('reservations.export.excel');
        Route::put('reservations/{id}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
        Route::put('reservations/{id}/rejected', [ReservationController::class, 'rejected'])->name('reservations.rejected');
        Route::delete('reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.delete');
    });

    // KARYAWAN 
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.create');
        Route::put('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });

    // Dashboard 
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);


    // RESERVATION LOG 
    Route::middleware(['auth:api', 'role:admin|karyawan'])->group(function () {
        Route::prefix('reservations')->group(function () {
            Route::get('{id}/logs', [ReservationLogController::class, 'index']);
            Route::get('logs/{id}', [ReservationLogController::class, 'show']);
        });
    });


    Route::options('{any}', function () {
        return response()->json([], 200);
    })->where('any', '.*');
});
