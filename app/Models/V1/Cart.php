<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'shopping_cart';
    protected $guarded = [];
    protected $casts = [
        'price' => 'double',
        'discount_value' => 'double',
        'total_price' => 'double'
    ];
    protected $cast = [
        'discount_price' => 'double',
        'total_price' => 'double',
        'price' => 'double',
        'qty' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\V1\Product', 'product_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\V1\Type', 'type_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\V1\Store', 'store_id', 'id');
    }
}
