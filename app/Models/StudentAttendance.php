<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_no','section_schedule_id','device_id',
        'class_date','time_in','time_out','status'
    ];

    protected $casts = [
        'class_date' => 'date',
        'time_in'    => 'datetime',
        'time_out'   => 'datetime',
    ];

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_no'); }
    public function schedule(): BelongsTo { return $this->belongsTo(SectionSchedule::class, 'section_schedule_id'); }
    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
}
