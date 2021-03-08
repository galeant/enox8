<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'report';
    protected $guarded = [];

    public function reporter(){
        return $this->belongsTo('App\Models\V1\User','user_id','id');
    }

    public function reported(){
        switch($this->relation_type){
            case 'user';
                return $this->belongsTo('App\Models\V1\User','relation_id','id')->where('relation_type','user');
                break;
            case 'product';
                return $this->belongsTo('App\Models\V1\Product','relation_id','id')->where('relation_type','product');
                break;
            case 'store';
                return $this->belongsTo('App\Models\V1\Store','relation_id','id')->where('relation_type','store');
                break;
        }
    }
}
