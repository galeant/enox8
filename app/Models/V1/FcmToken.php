<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'fcm_token';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }
}
