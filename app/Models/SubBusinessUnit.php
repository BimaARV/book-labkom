<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubBusinessUnit extends Model
{
    use HasFactory;

    protected $fillable = ['business_unit_id', 'name'];

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
