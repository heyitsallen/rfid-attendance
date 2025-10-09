<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname','middlename','lastname','email','password','status','profile_photo',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* -------------------------
     | Accessors
     * ------------------------*/
    public function fullName(): Attribute
    {
        return Attribute::get(function () {
            return trim($this->firstname.' '.($this->middlename ? $this->middlename.' ' : '').$this->lastname);
        });
    }

    /* -------------------------
     | Scopes
     * ------------------------*/
    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeInactive($q){ return $q->where('status', 'inactive'); }

    /* -------------------------
     | Roles
     * ------------------------*/
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['is_active','assigned_at','revoked_at'])
            ->withTimestamps();
    }

    public function activeRoles()
    {
        return $this->roles()->wherePivot('is_active', true);
    }

    public function hasRole(string $name): bool
    {
        return $this->activeRoles()->where('roles.name', $name)->exists();
    }

    /* -------------------------
     | Profiles
     * ------------------------*/
    public function studentProfile() { return $this->hasOne(StudentProfile::class); }
    public function facultyProfile() { return $this->hasOne(FacultyProfile::class); }

    /* -------------------------
     | Cards
     * ------------------------*/
    public function cards() { return $this->hasMany(Card::class); }

    public function activeCard() { return $this->hasOne(Card::class)->where('is_active', true)->latestOfMany(); }

    /* -------------------------
     | Academics (as student)
     * ------------------------*/
    public function sectionEnrollments() { return $this->hasMany(SectionEnrollment::class, 'student_id'); }

    public function studentAttendances() { return $this->hasMany(StudentAttendance::class, 'student_id'); }

    /* -------------------------
     | Academics (as faculty)
     * ------------------------*/
    public function facultyAssignments() { return $this->hasMany(FacultyAssignedSubject::class, 'faculty_id'); }
}
