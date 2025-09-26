<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    protected $fillable = ['name','date_start','date_end'];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
    ];

    public function assignedSubjects(): HasMany { return $this->hasMany(FacultyAssignedSubject::class); }
}
