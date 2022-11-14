<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'client_name',
        'org_id',
        'org_type',
        'sid',
        'token',
        'oauth_refresh_token',
        'allow_security_flag',
        'allow_AI_flag',
        'client_id',
        'client_secret',
        'name_space_sf',
        'client_email',
        'is_allow_email',
        'is_email_503_allow',
        'is_allow_short_url',
        'short_url_access_token',
        'short_url_created_at',
        'short_url_updated_at',
        'status'
    ];


    public function numbers()
    {
        return $this->hasMany(\App\Models\Number::class);
    }
}
