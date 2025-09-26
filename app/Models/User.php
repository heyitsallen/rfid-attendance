<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'password',
        'role',
        'student_id',
        'employee_no',
        'section_id',
        'status',
        'profile_photo', 
    ];

    protected $hidden = ['password','remember_token'];

    // Relationships
    public function cards(): HasMany { return $this->hasMany(Card::class); }
    public function assignedSubjects(): HasMany { return $this->hasMany(FacultyAssignedSubject::class, 'faculty_id'); }
    public function sectionSchedules(): HasMany { return $this->hasMany(SectionSchedule::class, 'faculty_id'); }
    public function facultyAttendances(): HasMany { return $this->hasMany(FacultyAttendance::class, 'faculty_id'); }
    public function studentAttendances(): HasMany { return $this->hasMany(StudentAttendance::class, 'student_id'); }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->firstname.' '.($this->middlename ? $this->middlename.' ' : '').$this->lastname);
    }

    // Scopes
    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeFaculty($q) { return $q->where('role', 'faculty'); }
    public function scopeStudent($q) { return $q->where('role', 'student'); }
}
