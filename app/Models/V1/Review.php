<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    // use SoftDeletes;

    protected $table = 'review';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\V1\Transaction', 'transaction_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\V1\Product', 'product_id', 'id');
    }

    public function product_type()
    {
        return $this->belongsTo('App\Models\V1\Type', 'type_id', 'id');
    }

    public function image()
    {
        return $this->hasMany('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'review');
    }
}
