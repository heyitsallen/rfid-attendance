<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyAssignedSubject extends Model
{
    protected $fillable = [
        'faculty_id','subject_curriculum_id','school_year_id','semester_id'
    ];

    public function faculty() { return $this->belongsTo(User::class, 'faculty_id'); }
    public function subject() { return $this->belongsTo(SubjectCurriculum::class, 'subject_curriculum_id'); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
    public function semester() { return $this->belongsTo(Semester::class); }

    public function sectionSchedules() { return $this->hasMany(SectionSchedule::class); }
}
