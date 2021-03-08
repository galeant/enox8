<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tag';
    protected $guarded = [];
    
    public function product(){
        return $this->belongsToMany('App\Models\V1\Product','product_to_tag','tag_id','product_id');
    }
}
