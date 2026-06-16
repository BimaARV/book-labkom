<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcDamage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function labPc()
    {
        return $this->belongsTo(LabPc::class);
    }
}
