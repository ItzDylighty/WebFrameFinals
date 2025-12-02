<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminSlotController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyticsController;
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
            $reservationDate = optional($reservation->reservation_date)->format('Y-m-d');

            return $reservation->status === 'approved'
                && $reservationDate !== null
                && $reservationDate >= $today;
        })->count();

        $completedCount = $allReservations->where('status', 'completed')->count();

        $statusCounts = [
            'pending' => $allReservations->where('status', 'pending')->count(),
            'approved' => $allReservations->where('status', 'approved')->count(),
            'rejected' => $allReservations->where('status', 'rejected')->count(),
        ];

        return view('home', [
            'recentReservations' => $recentReservations,
            'allReservations' => $allReservations,
            'stats' => [
                'total' => $allReservations->count(),
                'upcoming' => $upcomingCount,
                'completed' => $completedCount,
            ],
            'statusCounts' => $statusCounts,
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

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/reservations', function () {
        return view('admin.reservations');
    })->name('admin.reservations');

    Route::get('/admin/walk-in', function () {
        return view('admin.walkin');
    })->name('admin.walkin');

    Route::get('/admin/analytics', function () {
        return view('admin.analytics');
    })->name('admin.analytics');

    Route::prefix('admin/api/reservations')->group(function () {
        Route::get('/', [AdminReservationController::class, 'index']);
        Route::post('/{reservation}/reject', [AdminReservationController::class, 'reject']);
    });

    Route::prefix('admin/api')->group(function () {
        Route::post('/reservations/{reservation}/assign', [AdminSlotController::class, 'assignReservation']);

        Route::prefix('slots')->group(function () {
            Route::get('/', [AdminSlotController::class, 'index']);
            Route::post('/{slot}/walk-in', [AdminSlotController::class, 'walkIn']);
            Route::post('/{slot}/check-in', [AdminSlotController::class, 'checkIn']);
            Route::post('/{slot}/vacate', [AdminSlotController::class, 'vacate']);
        });

        Route::prefix('analytics')->group(function () {
            Route::get('/overview', [AnalyticsController::class, 'overview']);
            Route::get('/reservations-by-day', [AnalyticsController::class, 'reservationsByDay']);
            Route::get('/occupancy-by-hour', [AnalyticsController::class, 'occupancyByHour']);
            Route::get('/utilization-by-area', [AnalyticsController::class, 'utilizationByArea']);
        });
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
