<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'message';
    protected $fillable = [
        'subject',
        'content',
        'user_id',
        'store_id',
        'is_send'
    ];

    protected $appends = [
        'username',
        'storename'
    ];

    protected $hidden = [
        'user','store'
    ];
    public function user(){
        return $this->belongsTo('App\Models\V1\User','user_id','id');
    }

    public function store(){
        return $this->belongsTo('App\Models\V1\Store','store_id','id');
    }

    public function getUserNameAttribute(){
        return $this->user->first_name.' '.$this->user->last_name;
    }

    public function getStoreNameAttribute(){    
        return $this->store->name;
    }
}
