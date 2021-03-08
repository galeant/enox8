<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    // use SoftDeletes;
    protected $table = 'blog';

    protected $guarded = [];

    protected $appends = [
        'physical_path_banner'
    ];


    public function tag()
    {
        return $this->belongsToMany('App\Models\V1\BlogAttribute', 'blog_to_attribute', 'blog_id', 'attribute_id')->where('type', 'tag');
    }

    public function category()
    {
        return $this->belongsToMany('App\Models\V1\BlogAttribute', 'blog_to_attribute', 'blog_id', 'attribute_id')->where('type', 'category');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\V1\User', 'created_by', 'id');
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function getExcerptAttribute()
    {
        return substr($this->content, 0, 100);
    }

    public function getPhysicalPathBannerAttribute()
    {
        $return = NULL;
        if ($this->banner !== NULL) {
            $return = str_replace('storage', 'public', $this->banner);
        }
        return $return;
    }
}
