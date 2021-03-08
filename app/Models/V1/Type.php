<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    // use SoftDeletes;
    protected $table = 'product_type';

    protected $guarded = [];
    protected $appends = [
        'discount_price',
        'recalculate_discount',
        'image_physical_path'
    ];

    protected $casts = [
        'price' => 'double',
        'stock' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\V1\Product', 'product_id', 'id');
    }

    public function wishlist()
    {
        return $this->belongsToMany('App\Models\V1\User', 'user_wishlist', 'type_id', 'user_id')->withPivot('product_id');
    }

    public function getDiscountPriceAttribute()
    {
        $now = Carbon::now();
        $discount_start_date = Carbon::parse($this->discount_effective_start_date);
        $discount_end_date = Carbon::parse($this->discount_effective_end_date);
        if ($now->between($discount_start_date, $discount_end_date)) {
            switch ($this->discount_unit) {
                case 'decimal':
                    return $this->price - $this->discount_value;
                    break;
                case 'percentage':
                    return $this->price - (($this->price * $this->discount_value) / 100);
                    break;
            }
        } else {
            return NULL;
        }
    }
    public function getRecalculateDiscountAttribute()
    {
        $price = $this->discount_price;
        if ($this->discount_price === NULL) {
            $price = $this->price;
        }
        $now = Carbon::now();
        if (isset($this->product)) {
            foreach ($this->product->all_discount as $pr) {
                $discount_start_date = Carbon::parse($pr['effective_start_date']);
                $discount_end_date = Carbon::parse($pr['effective_end_date']);
                if ($now->between($discount_start_date, $discount_end_date) && strtolower($pr['status']) === 'publish') {
                    switch ($pr['unit']) {
                        case 'decimal':
                            $price = $price - $pr['value'];
                            break;
                        case 'percentage':
                            $price =  $price - (($this->price * $pr['value']) / 100);
                            break;
                    }
                }
            }
        }
        if ($price == $this->price) {
            return NULL;
        } else {
            return $price;
        }
    }

    public function getImagePhysicalPathAttribute()
    {
        $return = NULL;
        if ($this->product !== NULL) {
            $return = str_replace('storage', 'public', $this->image);
        }
        return $return;
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }
}
