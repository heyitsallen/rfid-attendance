<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_number','name'];

    public function devices() { return $this->hasMany(Device::class); }
    public function sectionSchedules() { return $this->hasMany(SectionSchedule::class); }
}
