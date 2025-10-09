<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_id','section_schedule_id','device_id',
        'class_date','time_in','time_out','status'
    ];

    protected $casts = [
        'class_date' => 'date',
        'time_in'    => 'datetime',
        'time_out'   => 'datetime',
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function sectionSchedule() { return $this->belongsTo(SectionSchedule::class); }
    public function device() { return $this->belongsTo(Device::class); }
}
