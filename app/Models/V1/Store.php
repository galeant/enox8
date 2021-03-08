<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;
    protected $table = 'store';
    protected $guarded = [];

    public function product()
    {
        return $this->hasMany('App\Models\V1\Product', 'store_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany('App\Models\V1\Transaction', 'store_id', 'id');
    }

    public function firstAdmin()
    {
        return $this->hasOne('App\Models\V1\Admin', 'store_id', 'id')->orderBy('created_at', 'asc');
    }


    public function country()
    {
        return $this->belongsTo('App\Models\V1\Country');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\V1\Province');
    }

    public function regency()
    {
        return $this->belongsTo('App\Models\V1\Regency');
    }
    public function district()
    {
        return $this->belongsTo('App\Models\V1\District');
    }

    public function village()
    {
        return $this->belongsTo('App\Models\V1\Village');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\V1\Currency');
    }

    public function message()
    {
        return $this->belongsToMany('App\Models\V1\User', 'message', 'store_id', 'user_id')->withPivot('content');
    }

    public function report()
    {
        return $this->hasMany('App\Models\V1\Report', 'relation_id', 'id')->where('relation_type', 'store');
    }

    public function user()
    {
        return $this->hasMany('App\Models\V1\User', 'store_id', 'id');
    }

    public function getLogoAttribute($v)
    {
        if ($v === NULL) {
            return asset('default/logodefault.png');
        } else {
            return asset($v);
        }
    }
}
