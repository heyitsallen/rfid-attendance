<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['is_active','assigned_at','revoked_at'])
            ->withTimestamps();
    }
}
