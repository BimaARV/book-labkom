<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function subUnits()
    {
        return $this->hasMany(SubBusinessUnit::class);
    }

    public function parent()
    {
        return $this->belongsTo(BusinessUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BusinessUnit::class, 'parent_id');
    }
}
