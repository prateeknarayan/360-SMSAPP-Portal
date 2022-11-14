<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessDataReceived extends Model
{
    use HasFactory;

    public function channel()
    {
        return $this->belongsTo(\App\Models\Channel::class);
    }

    public function process()
    {
        return $this->belongsTo(\App\Models\Process::class);
    }
}
