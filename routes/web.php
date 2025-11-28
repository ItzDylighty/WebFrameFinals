<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('reservations');
});

// Reservation Routes (API Endpoints)
Route::prefix('api/reservations')->group(function () {
    Route::get('/', [ReservationController::class, 'index']);           // Read all
    Route::post('/', [ReservationController::class, 'store']);          // Create
    Route::get('/{id}', [ReservationController::class, 'show']);        // Read one
    Route::put('/{id}', [ReservationController::class, 'update']);      // Update
    Route::delete('/{id}', [ReservationController::class, 'destroy']);  // Delete
});
