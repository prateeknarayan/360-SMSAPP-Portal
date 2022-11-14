<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

     public function platform()
    {
        return $this->belongsTo(\App\Models\SupportedPlatform::class, 'platform_id');
    }
}
