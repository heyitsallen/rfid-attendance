<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SectionSchedule extends Model
{
    protected $fillable = [
        'section_id','faculty_assigned_subject_id','room_id','day','start_time','end_time'
    ];

    /* -------------------------
     | Relations
     * ------------------------*/
    public function section()    { return $this->belongsTo(Section::class); }
    public function assignment() { return $this->belongsTo(FacultyAssignedSubject::class, 'faculty_assigned_subject_id'); }
    public function room()       { return $this->belongsTo(Room::class); }

    public function studentAttendances() { return $this->hasMany(StudentAttendance::class); }
    public function facultyAttendances() { return $this->hasMany(FacultyAttendance::class); }
    public function irregularEnrollments() { return $this->hasMany(StudentScheduleEnrollment::class, 'section_schedule_id'); }

    /* -------------------------
     | Computed accessors (convenience)
     | Use $schedule->faculty and $schedule->subject (not relations; read-only helpers)
     * ------------------------*/
    protected $appends = ['faculty','subject'];

    public function getFacultyAttribute()
    {
        // returns the User model if assignment is loaded; null otherwise
        return $this->relationLoaded('assignment') ? $this->assignment?->faculty : null;
    }

    public function getSubjectAttribute()
    {
        // returns the SubjectCurriculum model if assignment is loaded; null otherwise
        return $this->relationLoaded('assignment') ? $this->assignment?->subject : null;
    }

    /* -------------------------
     | Query scopes
     * ------------------------*/
    public function scopeForDay($q, string $day)
    {
        // Expect values like 'Mon'..'Sun'
        return $q->where('day', $day);
    }

    public function scopeCoversTime($q, string $timeHms)
    {
        // $timeHms like '14:23:00'
        return $q->whereTime('start_time', '<=', $timeHms)
                 ->whereTime('end_time',   '>=', $timeHms);
    }

    public function scopeInRoomIf($q, ?int $roomId)
    {
        return $roomId ? $q->where('room_id', $roomId) : $q;
    }

    public function scopeTaughtByIf($q, ?int $facultyId)
    {
        if (!$facultyId) return $q;
        return $q->whereHas('assignment', fn($a) => $a->where('faculty_id', $facultyId));
    }

    /* -------------------------
     | Handy helper (for Tinker/tests)
     * ------------------------*/
    public static function findCurrent(Carbon $now, ?int $roomId = null, ?int $facultyId = null)
    {
        $map = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        $dayEnum = $map[$now->isoWeekday()];
        $t = $now->format('H:i:s');

        return static::with(['assignment.faculty','assignment.subject','section','room'])
            ->forDay($dayEnum)
            ->coversTime($t)
            ->inRoomIf($roomId)
            ->taughtByIf($facultyId)
            ->first();
    }
}
