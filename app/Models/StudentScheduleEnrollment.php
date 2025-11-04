<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScheduleEnrollment extends Model
{
    protected $fillable = [
        'student_id','section_schedule_id','school_year_id','semester_id','reason','notes'
    ];

    public function student()          { return $this->belongsTo(User::class, 'student_id'); }
    public function schedule()         { return $this->belongsTo(SectionSchedule::class, 'section_schedule_id'); }
    public function schoolYear()       { return $this->belongsTo(SchoolYear::class); }
    public function semester()         { return $this->belongsTo(Semester::class); }
}
