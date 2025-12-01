<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'parkingSlot.area'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    public function approve(Reservation $reservation)
    {
        return $this->updateStatus($reservation, 'approved');
    }

    public function reject(Reservation $reservation)
    {
        return $this->updateStatus($reservation, 'rejected');
    }

    protected function updateStatus(Reservation $reservation, string $status)
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            abort(400, 'Invalid status');
        }

        $reservation->update([
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => "Reservation {$status} successfully.",
        ]);
    }
}
