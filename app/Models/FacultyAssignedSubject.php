<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyAssignedSubject extends Model
{
    protected $fillable = ['faculty_id','subject_curriculum_id','school_year_id','semester_id'];

    public function faculty(): BelongsTo { return $this->belongsTo(User::class, 'faculty_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(SubjectCurriculum::class, 'subject_curriculum_id'); }
    public function schoolYear(): BelongsTo { return $this->belongsTo(SchoolYear::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }

    public function sectionSchedules(): HasMany { return $this->hasMany(SectionSchedule::class); }
}
