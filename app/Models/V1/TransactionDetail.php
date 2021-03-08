<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{

    protected $table = 'transaction_detail';
    protected $guarded = [];


    public function transaction()
    {
        return $this->belongsTo('App\Models\V1\Transaction', 'transaction_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\V1\Product', 'product_id', 'id');
    }

    public function productType()
    {
        return $this->belongsTo('App\Models\V1\Type', 'type_id', 'id');
    }

    public function review()
    {
        return $this->hasOne('App\Models\V1\Review', 'transaction_detail_id', 'id');
    }

    public function complaint()
    {
        return $this->hasOne('App\Models\V1\Complaint', 'transaction_detail_id', 'id');
    }
}
