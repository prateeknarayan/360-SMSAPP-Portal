<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessRecord extends Model
{
    use HasFactory;

    public function Process()
    {
        return $this->belongsTo(\App\Models\Process::class);
    }

    public function platform()
    {
        return $this->belongsTo(\App\Models\SupportedPlatform::class, 'supported_platform_id');
    }

    public function datareceived()
    {
        return $this->hasOne(\App\Models\DataReceived::class);
    }
}
