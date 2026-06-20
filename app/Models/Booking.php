<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'report_images' => 'array',
        'is_clean' => 'boolean',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function getLabNameAttribute()
    {
        if ($this->is_all_labs) {
            return Laboratory::getAllLabsName();
        }
        return $this->laboratory ? $this->laboratory->name : '-';
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function subBusinessUnit()
    {
        return $this->belongsTo(SubBusinessUnit::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(BookingChangeRequest::class);
    }
}
