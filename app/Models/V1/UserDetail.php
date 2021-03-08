<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_detail';
    protected $guarded = [];

    protected $appends = [
        'fullname',
        'avatar_physical_path'
    ];

    public function user(){
        return $this->belongsTo('App\Models\V1\User','user_id','id');
    }

    public function getFullnameAttribute($value){
        return $this->firstname.' '.$this->firstname;
    }
    public function getAvatarPhysicalPathAttribute(){
        $return = NULL;
        if($this->avatar !== NULL){
            $return = str_replace('storage/','public/',$this->avatar); 
        }
        return $return;
    }
}
