<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'category';
    protected $guarded = [];
    protected $hidden = [
        'parent',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function children()
    {
        return $this->hasMany('App\Models\V1\Category', 'parent_id', 'id')->select('id', 'slug', 'name', 'parent_id', 'description');
        // ->with('children');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\V1\Category', 'parent_id', 'id');
    }

    public function discount()
    {
        return $this->belongsToMany('App\Models\V1\Discount', 'discount_to_relation', 'relation_id', 'discount_id')->wherePivot('type', 'category');
    }

    public function product()
    {
        return $this->belongsToMany('App\Models\V1\Product', 'product_to_category', 'category_id', 'product_id');
    }

    public function voucher()
    {
        return $this->belongsToMany('App\Models\V1\Voucher', 'voucher_to_relation', 'relation_id', 'voucher_id')->wherePivot('type', 'category');
    }

    public function icon()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'category_icon');
    }

    public function thumbnail()
    {
        return $this->hasOne('App\Models\V1\Image', 'relation_id', 'id')->where('relation_to', 'category_thumbnail');
    }
}
