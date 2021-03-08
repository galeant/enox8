<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';
    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo('App\Models\V1\Country', 'country_id', 'id');
    }

    public function regency()
    {
        return $this->hasMany('App\Models\V1\Regency', 'province_id', 'id');
    }
}
