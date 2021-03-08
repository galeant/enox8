<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    protected $table = 'regencies';
    protected $guarded = [];

    public function province(){
        return $this->belongsTo('App\Models\V1\Province','province_id','id');
    }

    public function district(){
        return $this->hasMany('App\Models\V1\District','regency_id','id');
    }
}
