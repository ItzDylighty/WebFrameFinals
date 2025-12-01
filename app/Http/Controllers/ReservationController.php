<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of all reservations.
     */
    public function index(Request $request)
    {
        $reservations = Reservation::where('user_id', $request->user()->id)
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required|date_format:H:i',
            'parking_no' => 'required|in:A,B,C,D,E,F',
            'phone_no' => 'required|string|max:15',
        ]);

        // Enforce capacity: max 20 reservations per parking area per date
        $existingCount = Reservation::where('parking_no', $validated['parking_no'])
            ->whereDate('reservation_date', $validated['reservation_date'])
            ->count();

        if ($existingCount >= 20) {
            return response()->json([
                'success' => false,
                'message' => 'Parking ' . $validated['parking_no'] . ' is full for that date (20/20 slots used).',
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => 'Reservation created successfully.',
        ], 201);
    }

    /**
     * Display the specified reservation.
     */
    public function show($id)
    {
        // TODO: Implement database query once migration is set up
        // $reservation = Reservation::find($id);

        return response()->json([
            'success' => false,
            'message' => 'Database not yet implemented. Cannot retrieve reservation.'
        ], 404);
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement database query once migration is set up
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'plate_number' => 'sometimes|string|max:20',
            'reservation_date' => 'sometimes|date',
            'reservation_time' => 'sometimes|date_format:H:i',
            'parking_no' => 'sometimes|string|max:10',
            'phone_no' => 'sometimes|string|max:15',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Database not yet implemented. Cannot update reservation.'
        ], 404);
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy($id)
    {
        // TODO: Implement database query once migration is set up

        return response()->json([
            'success' => false,
            'message' => 'Database not yet implemented. Cannot delete reservation.'
        ], 404);
    }
}
