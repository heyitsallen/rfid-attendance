<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionEnrollment extends Model
{
    protected $fillable = [
        'student_id','section_id','school_year_id','semester_id','date_enrolled'
    ];

    protected $casts = ['date_enrolled' => 'date'];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
    public function semester() { return $this->belongsTo(Semester::class); }

    /* Helpful scope for current term */
    public function scopeForTerm($q, $syId, $semId)
    {
        return $q->where('school_year_id', $syId)->where('semester_id', $semId);
    }
}
