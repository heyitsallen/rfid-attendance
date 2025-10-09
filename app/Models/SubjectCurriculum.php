<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectCurriculum extends Model
{
    protected $fillable = ['description','unit','lecture','laboratory','course_code'];

    public function facultyAssignments() { return $this->hasMany(FacultyAssignedSubject::class); }
}
