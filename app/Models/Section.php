<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['name','year_level_id'];

    public function yearLevel(): BelongsTo { return $this->belongsTo(YearLevel::class); }
    public function schedules(): HasMany { return $this->hasMany(SectionSchedule::class); }
}
