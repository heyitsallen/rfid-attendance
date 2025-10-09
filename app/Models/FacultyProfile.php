<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyProfile extends Model
{
    protected $fillable = ['user_id','employee_no'];

    public function user() { return $this->belongsTo(User::class); }
}
