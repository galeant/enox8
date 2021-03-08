<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $table = 'villages';
    protected $guarded = [];

    public function district(){
        return $this->belongsTo('App\Models\V1\District','district_id','id');
    }
}
