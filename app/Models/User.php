<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname','middlename','lastname','email','password','status','profile_photo',
        // keep legacy cols if they still exist (optional): 'first_name','middle_name','last_name','role','role_id'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // expose these computed attributes in arrays/json
    protected $appends = ['role_name','full_name'];

    /* -------------------------
     | Scopes
     * ------------------------*/
    public function scopeActive($q)   { return $q->where('status', 'active'); }
    public function scopeInactive($q) { return $q->where('status', 'inactive'); }

    /* -------------------------
     | Roles (many-to-many)
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
        $name = strtolower(trim($name));
        return $this->activeRoles()
            ->whereRaw('LOWER(roles.name) = ?', [$name])
            ->exists();
    }

    // Helpers (optional)
    public function isAdmin(): bool   { return $this->hasRole('admin'); }
    public function isFaculty(): bool { return $this->hasRole('faculty'); }
    public function isStudent(): bool { return $this->hasRole('student'); }

    /* -------------------------
     | Profiles
     * ------------------------*/
    public function studentProfile() { return $this->hasOne(StudentProfile::class); }
    public function facultyProfile() { return $this->hasOne(FacultyProfile::class); }

    /* -------------------------
     | Cards
     * ------------------------*/
    public function cards() { return $this->hasMany(Card::class); }

    public function activeCard()
    {
        return $this->hasOne(Card::class)->where('is_active', true)->latestOfMany();
    }

    /* -------------------------
     | Academics (as student)
     * ------------------------*/
    public function sectionEnrollments() { return $this->hasMany(SectionEnrollment::class, 'student_id'); }
    public function studentAttendances() { return $this->hasMany(StudentAttendance::class, 'student_id'); }

    /* -------------------------
     | Academics (as faculty)
     * ------------------------*/
    public function facultyAssignments() { return $this->hasMany(FacultyAssignedSubject::class, 'faculty_id'); }

    /* -------------------------
     | Computed attributes
     * ------------------------*/
    public function getRoleNameAttribute(): ?string
    {
        // Prefer eager-loaded many-to-many (first active role by convention)
        if ($this->relationLoaded('roles')) {
            $role = $this->roles->firstWhere('pivot.is_active', true) ?? $this->roles->first();
            if ($role?->name) return strtolower($role->name);
        }

        // Fallbacks for legacy columns if still present
        if (array_key_exists('role', $this->attributes)) {
            $raw = strtolower((string) $this->attributes['role']);
            $map = ['1' => 'admin', '2' => 'faculty', '3' => 'student', 'administrator' => 'admin', 'teacher' => 'faculty'];
            return $map[$raw] ?? $raw;
        }

        if (array_key_exists('role_id', $this->attributes)) {
            $mapById = [1 => 'admin', 2 => 'faculty', 3 => 'student'];
            return $mapById[$this->attributes['role_id']] ?? null;
        }

        return null;
    }

    public function getFullNameAttribute(): string
    {
        $first  = $this->first_name  ?? $this->firstname  ?? '';
        $middle = $this->middle_name ?? $this->middlename ?? '';
        $last   = $this->last_name   ?? $this->lastname   ?? '';

        $full = trim(preg_replace('/\s+/', ' ', "$first $middle $last"));
        return $full;
    }

}
