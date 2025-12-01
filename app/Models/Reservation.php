<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'plate_number',
        'reservation_date',
        'reservation_time',
        'parking_no',
        'preferred_parking_no',
        'parking_slot_id',
        'phone_no',
        'status',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class);
    }
}
