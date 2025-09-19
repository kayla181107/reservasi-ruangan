<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\FixedScheduleController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']); // default role karyawan
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', function (Request $request) {
        return $request->user()->load('roles');
    })->middleware('auth:api');
});


Route::middleware('auth:api')->group(function () {

    
    Route::get('/rooms', [RoomController::class, 'index']);       
    Route::get('/rooms/{id}', [RoomController::class, 'show']);  
    
    Route::middleware('role:admin')->group(function () {
        Route::post('/rooms', [RoomController::class, 'store']);      
        Route::put('/rooms/{id}', [RoomController::class, 'update']); 
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy']); 
    });

    
    Route::get('/fixed-schedules', [FixedScheduleController::class, 'index']);
    Route::get('/fixed-schedules/{id}', [FixedScheduleController::class, 'show']);
    
    Route::middleware('role:admin')->group(function () {
        Route::post('/fixed-schedules', [FixedScheduleController::class, 'store']);
        Route::put('/fixed-schedules/{id}', [FixedScheduleController::class, 'update']);
        Route::delete('/fixed-schedules/{id}', [FixedScheduleController::class, 'destroy']);
    });

    
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']); 

    Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->middleware('role:karyawan|admin'); // user bisa cancel punya dia, admin bisa juga
    
    Route::middleware('role:admin')->group(function () {
        Route::put('/reservations/{id}/approve', [ReservationController::class, 'approve']);
        Route::put('/reservations/{id}/reject', [ReservationController::class, 'reject']);
    });
});
