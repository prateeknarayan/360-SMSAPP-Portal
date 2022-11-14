<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * defines the users belongs to permission
     */
    // public function users()
    // {
    //     return $this->hasMany(\App\Models\User::class);
    // }
}
