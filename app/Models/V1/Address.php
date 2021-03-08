<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'user_address';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo('App\Models\V1\User','user_id','id');
    }

    public function country(){
        return $this->belongsTo('App\Models\V1\Country','country_id','id');
    }

    public function province(){
        return $this->belongsTo('App\Models\V1\Province','province_id','id');
    }

    public function regency(){
        return $this->belongsTo('App\Models\V1\Regency','regency_id','id');
    }

    public function district(){
        return $this->belongsTo('App\Models\V1\District','district_id','id');
    }

    public function village(){
        return $this->belongsTo('App\Models\V1\Village','village_id','id');
    }
}
