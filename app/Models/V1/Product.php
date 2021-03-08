<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'product';
    protected $guarded = [];

    protected $appends = [
        'main_image_physical_path',
        'all_discount'
        // 'origin_currency',
        // 'convert_currency'
    ];

    protected $casts = [
        'display_price' => 'double',
        'rating' => 'double'
    ];

    // protected $hidden = [
    //     'store'
    // ];

    public function category()
    {
        return $this->belongsToMany('App\Models\V1\Category', 'product_to_category', 'product_id', 'category_id')->withPivot('selected');
    }


    public function selectedCategory()
    {
        return $this->belongsToMany('App\Models\V1\Category', 'product_to_category', 'product_id', 'category_id')->withPivot('selected')->wherePivot('selected', true);
    }

    public function tag()
    {
        return $this->belongsToMany('App\Models\V1\Tag', 'product_to_tag', 'product_id', 'tag_id');
    }

    public function type()
    {
        return $this->hasMany('App\Models\V1\Type', 'product_id', 'id');
    }

    public function defaultType()
    {
        return $this->hasOne('App\Models\V1\Type', 'product_id', 'id')->where('is_default', true);
    }

    public function cart()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }

    // public function wishlist()
    // {
    //     return $this->belongsToMany('App\Models\V1\User', 'user_wishlist', 'product_id', 'user_id');
    // }

    public function discount()
    {
        return $this->belongsToMany('App\Models\V1\Discount', 'discount_to_relation', 'relation_id', 'discount_id')->wherePivot('type', 'product');
    }

    public function transaction()
    {
    }

    public function images()
    {
        return $this->hasMany('App\Models\V1\Image', 'relation_id', 'id')->where(['relation_to' => 'product']);
    }


    public function store()
    {
        return $this->belongsTo('App\Models\V1\Store', 'store_id', 'id')->withTrashed();
    }


    public function stock()
    {
        return $this->hasMany('App\Models\V1\Stock', 'product_id', 'id');
    }

    public function review()
    {
        return $this->hasMany('App\Models\V1\Review', 'product_id', 'id');
    }

    public function comment()
    {
        return $this->hasMany('App\Models\V1\Comment', 'product_id', 'id');
    }

    public function voucher()
    {
        return $this->belongsToMany('App\Models\V1\Voucher', 'voucher_to_relation', 'relation_id', 'voucher_id')->wherePivot('type', 'product');
    }

    // public function report(){
    //     return $this->hasMany('App\Models\V1\Report','relation_id','id')->where('relation_type','product');
    // }
    // // ACESSOR
    // public function getOriginCurrencyAttribute(){
    //     return 'IDR';
    // }
    // public function getConvertCurrencyAttribute(){
    //     $return = NULL;
    //     if($this->store->currency !== NULL){
    //         $return = $this->store->currency->code;
    //     }
    //     return $return;
    // }

    // public function getMainImageAttribute($value){
    //     return asset($value);
    // }

    public function getWeightAttribute($v)
    {
        if ($v === NULL) {
            return 1;
        }
        return $v;
    }
    public function getMainImagePhysicalPathAttribute()
    {
        $return = NULL;
        if ($this->main_image !== NULL) {
            $exp = explode('/', $this->main_image);
            $exp[0] = 'public';
            return implode('/', $exp);
        }
        return $return;
    }

    public function getAllDiscountAttribute()
    {
        $discount = [];

        // IN PRODUCT
        foreach ($this->discount as $dsc) {
            if (!in_array($dsc->id, array_column($discount, 'id'))) {
                $discount[] = [
                    'id' => $dsc->id,
                    'value' => $dsc->value,
                    'unit' => $dsc->unit,
                    'status' => $dsc->status,
                    'effective_start_date' => $dsc->effective_start_date,
                    'effective_end_date' => $dsc->effective_end_date,
                ];
            }
        }
        // IN CATEGORY
        foreach ($this->category as $ct) {
            foreach ($ct->discount as $cdsc) {
                if (!in_array($cdsc->id, array_column($discount, 'id'))) {
                    $discount[] = [
                        'id' => $cdsc->id,
                        'value' => $cdsc->value,
                        'unit' => $cdsc->unit,
                        'status' => $cdsc->status,
                        'effective_start_date' => $cdsc->effective_start_date,
                        'effective_end_date' => $cdsc->effective_end_date,
                    ];
                }
            }
        }
        return $discount;
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function report()
    {
        return $this->hasMany('App\Models\V1\Report', 'relation_id', 'id')->where('relation_type', 'product');
    }
}
