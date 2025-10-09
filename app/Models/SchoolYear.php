<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = ['name','date_start','date_end'];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
    ];

    public function semesters() { return $this->hasMany(Semester::class); }

    public function cards() { return $this->hasMany(Card::class); }
}
