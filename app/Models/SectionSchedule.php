<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionSchedule extends Model
{
    protected $fillable = [
        'section_id','faculty_assigned_subject_id','room_id','day','start_time','end_time'
    ];

    public function section() { return $this->belongsTo(Section::class); }
    public function assignment() { return $this->belongsTo(FacultyAssignedSubject::class, 'faculty_assigned_subject_id'); }
    public function room() { return $this->belongsTo(Room::class); }

    public function studentAttendances() { return $this->hasMany(StudentAttendance::class); }
    public function facultyAttendances() { return $this->hasMany(FacultyAttendance::class); }

    // Convenience shortcuts (derived; not DB columns)
    public function faculty() { return $this->assignment?->faculty(); }
    public function subject() { return $this->assignment?->subject(); }
}
