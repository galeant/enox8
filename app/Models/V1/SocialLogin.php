<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class SocialLogin extends Model
{
    protected $table = 'user_social_login';
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }
}
