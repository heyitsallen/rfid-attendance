<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = ['serial_no','token','room_id','description'];

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
}
