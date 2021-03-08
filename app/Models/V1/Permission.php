<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $guarded = [];

    public function role(){
        return $this->belongsToMany('App\Models\V1\Role','role_to_permission','permission_id','role_id');
    }

    public function getDescriptionAttribute($value){
        return json_decode($value);
    }
}
