<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name','year_level_id'];

    public function yearLevel() { return $this->belongsTo(YearLevel::class); }

    public function schedules() { return $this->hasMany(SectionSchedule::class); }

    public function enrollments() { return $this->hasMany(SectionEnrollment::class); }
}
