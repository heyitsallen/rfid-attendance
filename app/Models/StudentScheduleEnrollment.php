<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentScheduleEnrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id','section_schedule_id','school_year_id','semester_id','reason','notes'
    ];

    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function schedule()   { return $this->belongsTo(SectionSchedule::class, 'section_schedule_id'); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
    public function semester()   { return $this->belongsTo(Semester::class); }

    /** Quick scope: for a specific term */
    public function scopeForTerm($q, $syId, $semId) {
        return $q->where('school_year_id', $syId)->where('semester_id', $semId);
    }
}
