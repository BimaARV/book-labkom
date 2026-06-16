<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabPc extends Model
{
    protected $guarded = [];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function damages()
    {
        return $this->hasMany(PcDamage::class);
    }

    public function latestDamage()
    {
        return $this->hasOne(PcDamage::class)->latestOfMany();
    }
}
