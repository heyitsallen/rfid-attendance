<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $fillable = ['user_id','uid','is_active','issued_at','revoked_at','notes'];

    protected $casts = [
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
}
