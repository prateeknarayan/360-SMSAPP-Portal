<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportedPlatform extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function requestType()
    {
        return $this->belongsTo(\App\Models\RequestType::class);
    }

    public function channels()
    {
        return $this->belongsToMany('App\Models\Channel', 'supported_platform_channels', 
      'supported_platform_id', 'channel_id')->withPivot('configs');
    }   

    public function authTypes()
    {
        return $this->belongsToMany('App\Models\AuthenticationType', 'supported_platform_authentication_type', 
      'supported_platform_id', 'authentication_type_id')->withPivot('configs');
    }   
}
