<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;
use App\Models\Reservation;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $user = auth()->user();

        $allReservations = Reservation::where('user_id', $user->id)
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->get();

        $recentReservations = $allReservations->take(3);

        $today = now()->toDateString();

        $upcomingCount = $allReservations->filter(function ($reservation) use ($today) {
            return $reservation->reservation_date->format('Y-m-d') >= $today;
        })->count();

        $completedCount = $allReservations->filter(function ($reservation) use ($today) {
            return $reservation->reservation_date->format('Y-m-d') < $today;
        })->count();

        return view('home', [
            'recentReservations' => $recentReservations,
            'allReservations' => $allReservations,
            'stats' => [
                'total' => $allReservations->count(),
                'upcoming' => $upcomingCount,
                'completed' => $completedCount,
            ],
        ]);
    })->name('home');

    Route::get('/reservations', function () {
        return view('reservations');
    })->name('reservations');

    // Reservation Routes (API Endpoints)
    Route::prefix('api/reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);           // Read all
        Route::post('/', [ReservationController::class, 'store']);          // Create
        Route::get('/{id}', [ReservationController::class, 'show']);        // Read one
        Route::put('/{id}', [ReservationController::class, 'update']);      // Update
        Route::delete('/{id}', [ReservationController::class, 'destroy']);  // Delete
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
