<?php

namespace App\Http\Controllers;

use App\Models\ParkingArea;
use App\Models\ParkingSlot;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSlotController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $areas = ParkingArea::with(['slots' => function ($query) {
            $query->orderBy('slot_number')
                ->with(['activeReservation.user', 'area']);
        }])->orderBy('code')->get();

        // Compute UI status per slot scoped to TODAY so future reservations don't block current usage
        foreach ($areas as $area) {
            foreach ($area->slots as $slot) {
                // Preserve occupied state from DB (physical presence wins)
                if ($slot->status === 'occupied') {
                    continue;
                }

                $hasApprovedToday = Reservation::where('parking_slot_id', $slot->id)
                    ->whereDate('reservation_date', $today)
                    ->where('status', 'approved')
                    ->whereNull('checked_out_at')
                    ->exists();

                // Override status just for the response (do not persist)
                $slot->status = $hasApprovedToday ? 'reserved' : 'vacant';

                // Also override exposed activeReservation to today's reservation (for UI details panel)
                $todaysReservation = Reservation::with('user')
                    ->where('parking_slot_id', $slot->id)
                    ->whereDate('reservation_date', $today)
                    ->whereNull('checked_out_at')
                    ->latest()
                    ->first();
                $slot->setRelation('activeReservation', $todaysReservation);

                // Also provide a list of upcoming reservations for this slot (today and future), approved only
                $futureReservations = Reservation::with('user')
                    ->where('parking_slot_id', $slot->id)
                    ->whereDate('reservation_date', '>=', $today)
                    ->where('status', 'approved')
                    ->orderBy('reservation_date')
                    ->orderBy('reservation_time')
                    ->get();
                $slot->setRelation('upcomingReservations', $futureReservations);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }

    public function assignReservation(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'parking_slot_id' => ['required', Rule::exists('parking_slots', 'id')],
        ]);

        if ($reservation->status !== 'pending') {
            abort(422, 'Only pending reservations can be assigned.');
        }

        return DB::transaction(function () use ($reservation, $data) {
            $slot = ParkingSlot::lockForUpdate()->with('area')->findOrFail($data['parking_slot_id']);

            // Current physical status (e.g., occupied today) does not block assigning for a different date.
            // We only guard against double-booking on the same reservation date below.

            // Prevent double-booking: the slot cannot be assigned to more than one reservation on the same date
            $existsSameDay = Reservation::where('parking_slot_id', $slot->id)
                ->whereDate('reservation_date', optional($reservation->reservation_date)->format('Y-m-d'))
                ->where('status', 'approved')
                ->exists();

            if ($existsSameDay) {
                abort(422, 'Slot already allocated for that date. Choose a different slot.');
            }

            $reservation->update([
                'parking_slot_id' => $slot->id,
                'parking_no' => $slot->area->code,
                'status' => 'approved',
            ]);

            return response()->json([
                'success' => true,
                'data' => $reservation->load('user', 'parkingSlot.area'),
                'message' => 'Reservation assigned to slot.',
            ]);
        });
    }

    public function walkIn(Request $request, ParkingSlot $slot)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20'],
            'phone_no' => ['nullable', 'string', 'max:20'],
        ]);

        return DB::transaction(function () use ($slot, $data) {
            $slot->refresh();

            if ($slot->status !== 'vacant') {
                abort(422, 'Slot is not available.');
            }

            $user = User::firstOrCreate(
                ['email' => 'walkin@eseepark.local'],
                [
                    'name' => 'Walk-in Customer',
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                ]
            );

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'plate_number' => $data['plate_number'],
                'reservation_date' => now()->toDateString(),
                'reservation_time' => now()->format('H:i'),
                'parking_no' => $slot->area->code,
                'parking_slot_id' => $slot->id,
                'phone_no' => $data['phone_no'] ?? '',
                'status' => 'approved',
                'checked_in_at' => now(),
            ]);

            $slot->update(['status' => 'occupied']);

            return response()->json([
                'success' => true,
                'data' => $reservation->load('parkingSlot.area'),
                'message' => 'Walk-in vehicle checked in.',
            ]);
        });
    }

    public function checkIn(Request $request, ParkingSlot $slot)
    {
        $data = $request->validate([
            'reservation_id' => ['required', Rule::exists('reservations', 'id')],
        ]);

        return DB::transaction(function () use ($slot, $data) {
            $slot = ParkingSlot::lockForUpdate()->findOrFail($slot->id);
            $reservation = Reservation::lockForUpdate()->findOrFail($data['reservation_id']);

            if ($reservation->parking_slot_id !== $slot->id) {
                abort(422, 'Reservation is not assigned to this slot.');
            }

            $reservation->update([
                'checked_in_at' => now(),
                'status' => 'approved',
            ]);

            $slot->update(['status' => 'occupied']);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle checked in.',
            ]);
        });
    }

    public function vacate(ParkingSlot $slot)
    {
        return DB::transaction(function () use ($slot) {
            $slot = ParkingSlot::lockForUpdate()->findOrFail($slot->id);

            $activeReservation = $slot->reservations()
                ->whereNull('checked_out_at')
                ->latest()
                ->first();

            if ($activeReservation) {
                $activeReservation->update([
                    'checked_out_at' => now(),
                    'status' => 'completed',
                ]);
            }

            $slot->update(['status' => 'vacant']);

            return response()->json([
                'success' => true,
                'message' => 'Slot vacated.',
            ]);
        });
    }
}
