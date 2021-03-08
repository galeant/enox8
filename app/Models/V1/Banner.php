<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';
    protected $guarded = [];


    public function webBanner()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'web_banner');
    }

    public function mobileBanner()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'mobile_banner');
    }

    public function relation()
    {
        if ($this->relation_to !== NULL) {
            switch ($this->relation_to) {
                case 'product':
                    return $this->belongsToMany('App\Models\V1\Product', 'banner_to_relation', 'banner_id', 'relation_id');
                    break;
                case 'voucher':
                    return $this->belongsToMany('App\Models\V1\Voucher', 'banner_to_relation', 'banner_id', 'relation_id');
                    break;
                case 'category':
                    return $this->belongsToMany('App\Models\V1\Category', 'banner_to_relation', 'banner_id', 'relation_id');
                    break;
            }
        }
        return NULL;
    }
}
