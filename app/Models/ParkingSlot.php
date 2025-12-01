<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParkingSlot extends Model
{
    protected $fillable = [
        'parking_area_id',
        'slot_number',
        'status',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(ParkingArea::class, 'parking_area_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function activeReservation(): HasOne
    {
        return $this->hasOne(Reservation::class)
            ->latestOfMany()
            ->whereNull('checked_out_at');
    }
}
