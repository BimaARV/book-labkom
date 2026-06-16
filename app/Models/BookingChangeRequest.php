<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingChangeRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requested_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requestedLaboratory()
    {
        return $this->belongsTo(Laboratory::class, 'requested_laboratory_id');
    }
}
