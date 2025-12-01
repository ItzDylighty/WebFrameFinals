<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingArea extends Model
{
    protected $fillable = [
        'name',
        'code',
        'total_slots',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class);
    }
}
