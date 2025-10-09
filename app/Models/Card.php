<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'user_id','uid','school_year_id','is_active','issued_at','revoked_at','notes'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'revoked_at'=> 'datetime',
        'is_active' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
