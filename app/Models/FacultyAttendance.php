<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyAttendance extends Model
{
    protected $fillable = [
        'faculty_id','section_schedule_id','device_id',
        'class_date','time_in','time_out','status'
    ];

    protected $casts = [
        'class_date' => 'date',
        'time_in'    => 'datetime',
        'time_out'   => 'datetime',
    ];

    public function faculty(): BelongsTo { return $this->belongsTo(User::class, 'faculty_id'); }
    public function schedule(): BelongsTo { return $this->belongsTo(SectionSchedule::class, 'section_schedule_id'); }
    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
}
