<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function labPcs()
    {
        return $this->hasMany(LabPc::class);
    }
}
