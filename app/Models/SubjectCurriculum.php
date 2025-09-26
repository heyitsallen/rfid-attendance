<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectCurriculum extends Model
{
    protected $fillable = ['description','unit','lecture','laboratory','course_code'];

    public function assignedSubjects(): HasMany { return $this->hasMany(FacultyAssignedSubject::class); }
}
