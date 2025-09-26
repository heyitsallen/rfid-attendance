<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YearLevel extends Model
{
    protected $fillable = ['name'];

    public function sections(): HasMany { return $this->hasMany(Section::class); }
}
