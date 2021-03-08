<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    protected $table = 'voucher';
    protected $guarded = [];

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function product()
    {
        return $this->belongsToMany('App\Models\V1\Product', 'voucher_to_relation', 'voucher_id', 'relation_id')->wherePivot('type', 'product')->withPivot('type');
    }

    public function category()
    {
        return $this->belongsToMany('App\Models\V1\Category', 'voucher_to_relation', 'voucher_id', 'relation_id')->wherePivot('type', 'category');
    }

    public function usage()
    {
        return $this->hasMany('App\Models\V1\VoucherUsage', 'voucher_code', 'code');
    }

    public function image()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'voucher');
    }
}
