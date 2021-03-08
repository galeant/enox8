<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'image';
    protected $guarded = [];
    protected $appends = [
        'physical_path'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\V1\Product', 'relation_id', 'id');
    }

    // public function getUrlAttribute($value){
    //     if(strpos($value, 'http://') === false && strpos($value, 'https://') === false && $value !== NULL){
    //         return asset($value);
    //     }
    //     return NULL;

    // }

    public function getPhysicalPathAttribute()
    {
        $return = NULL;
        if ($this->url !== NULL) {
            if ($this->product !== NULL) {
                $filename = str_replace('storage', 'public', $this->url);
            } else if ($this->campaign_type !== NULL) {
                $url = json_decode($this->url);
                $return = collect($url)->transform(function ($v) {
                    return str_replace('storage', 'public', $v);
                });
            }
        }

        return $return;
    }
}
