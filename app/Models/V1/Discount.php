<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;
    protected $table = 'discount';
    protected $guarded = [];
    protected $appends = [
        'banner_physical_path',
        'all_product'
    ];

    public function product(){
        return $this->belongsToMany('App\Models\V1\Product','discount_to_relation','discount_id','relation_id')->wherePivot('type','product')->withPivot('type');
    }

    public function category(){
        return $this->belongsToMany('App\Models\V1\Category','discount_to_relation','discount_id','relation_id')->wherePivot('type','category');
    }

    public function getBannerPhysicalPathAttribute(){
        $return = NULL;
        if($this->banner !== NULL){
            $return = str_replace('storage','public',$this->banner); 
        }
        return $return;
    }

    public function getStatusAttribute($value){
        return ucfirst($value);
    }

    public function getAllProductAttribute(){
        $product_id = [];
        foreach($this->product as $pr){
            $product_id[] = $pr->id;
        }
        foreach($this->category as $ct){
            foreach($ct->product as $pt){
                if(!in_array($pt->id, $product_id)){
                    $product_id[] = $pt->id;
                }
            }
        }
        return $product_id;
    }
}
