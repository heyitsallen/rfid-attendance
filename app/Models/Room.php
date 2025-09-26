<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['room_number','name'];

    public function devices(): HasMany { return $this->hasMany(Device::class); }

    // Optional convenience:
    public function sectionSchedules(): HasMany { return $this->hasMany(SectionSchedule::class); }
}
