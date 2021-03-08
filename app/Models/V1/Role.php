<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $guarded = [];

    public function permission(){
        return $this->belongsToMany('App\Models\V1\Permission','role_to_permission','role_id','permission_id');
    }
}
