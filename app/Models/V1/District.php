<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';
    protected $guarded = [];

    public function regency(){
        return $this->belongsTo('App\Models\V1\Regency','regency_id','id');
    }

    public function village(){
        return $this->hasMany('App\Models\V1\Village','district_id','id');
    }
}
