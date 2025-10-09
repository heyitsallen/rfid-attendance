<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyAttendance extends Model
{
    protected $fillable = [
        'section_schedule_id','device_id','class_date','time_in','time_out','status'
    ];

    protected $casts = [
        'class_date' => 'date',
        'time_in'    => 'datetime',
        'time_out'   => 'datetime',
    ];

    public function sectionSchedule() { return $this->belongsTo(SectionSchedule::class); }
    public function device() { return $this->belongsTo(Device::class); }

    // Convenience helper (two-hop; not a relationship)
    public function facultyUser()
    {
        return optional($this->sectionSchedule?->assignment)->faculty;
    }
}
