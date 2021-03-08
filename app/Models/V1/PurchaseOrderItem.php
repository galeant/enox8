<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    // use softDeletes;

    protected $table = 'purchase_order_item';

    protected $fillable = [
        'purchase_order_id',
        'product_name',
        'product_code',
        'price',
        'qty',
        'unit',
        'total_price',
        'tax'
    ];

    public function payment_order(){
        return $this->belongsTo('App\Models\V1\PurchaseOrder','purchase_order_id','id');
    }
}
