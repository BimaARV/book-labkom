<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getAllLabsName()
    {
        return \Illuminate\Support\Facades\Cache::remember('all_labs_name', 3600, function () {
            $labs = self::where('status', 'active')->orderBy('id')->get();
            return $labs->count() > 1 ? $labs->first()->name . ' - ' . $labs->last()->name : 'Semua Labkom';
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function labPcs()
    {
        return $this->hasMany(LabPc::class);
    }
}
