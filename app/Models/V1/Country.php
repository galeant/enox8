<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $guarded = [];

    public function province(){
        return $this->hasMany('App\Models\V1\Province','country_id','id');
    }
}
