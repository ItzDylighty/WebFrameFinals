<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'name',
        'plate_number',
        'reservation_date',
        'reservation_time',
        'parking_no',
        'phone_no',
    ];

    protected $casts = [
        'reservation_date' => 'date',
    ];
}
