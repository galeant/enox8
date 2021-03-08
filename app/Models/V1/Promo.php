<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promo';
    protected $guarded = [];

    public function image()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'promo');
    }

    public function discount()
    {
        return $this->belongsToMany('App\Models\V1\Discount', 'promo_to_relation', 'promo_id', 'relation_id')->wherePivot('relation_type', 'discount');
    }

    public function voucher()
    {
        return $this->belongsToMany('App\Models\V1\Voucher', 'promo_to_relation', 'promo_id', 'relation_id')->wherePivot('relation_type', 'voucher');
    }
}
