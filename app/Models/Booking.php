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
            return \Illuminate\Support\Facades\Cache::remember('all_labs_name', 3600, function () {
                $labs = \App\Models\Laboratory::where('status', 'active')->orderBy('id')->get();
                if ($labs->count() > 1) {
                    return $labs->first()->name . ' - ' . $labs->last()->name;
                }
                return 'Semua Labkom';
            });
        }
        return optional($this->laboratory)->name;
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
