<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $table = 'comment';
    protected $guarded = [];
    protected $appends = [
        'username',
        'super_admin',
        'admin',
        'customer'
    ];

    protected $hidden = [
        'user'
    ];

    public function parent(){
        return $this->belongsTo('App\Models\V1\Comment','parent_id','id')->withTrahsed()->with('parent');
    }

    public function children(){
        return $this->hasMany('App\Models\V1\Comment','parent_id','id')->select('id','content','parent_id','deleted_at','user_id','created_at')->with('children')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\V1\User','user_id','id');
    }

    public function product(){
        return $this->belongsTo('App\Models\V1\Product','product_id','id');
    }

    public function getContentAttribute($value){
        if($this->deleted_at !== NULL){
            $value = 'Comment has been deleted';
        }
        return $value;
    }

    public function getUsernameAttribute(){
        if(isset($this->user)){
            return $this->user->detail->firstname.' '.$this->user->detail->lastname;
        }else{
            return NULL;
        }
    }

    public function getSuperAdminAttribute(){
        if(isset($this->user)){
            return $this->user->can_access_super_admin;
        }else{
            return false;
        }
    }

    public function getAdminAttribute(){
        if(isset($this->user)){
            return $this->user->can_access_admin;
        }else{
            return false;
        }
    }

    public function getCustomerAttribute(){
        if(isset($this->user)){
            return $this->user->can_access_customer;
        }else{
            return false;
        }
    }
}
