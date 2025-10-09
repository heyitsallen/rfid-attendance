<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['serial_no','token','room_id','description'];

    public function room() { return $this->belongsTo(Room::class); }

    public function studentAttendances() { return $this->hasMany(StudentAttendance::class); }
    public function facultyAttendances() { return $this->hasMany(FacultyAttendance::class); }
}
