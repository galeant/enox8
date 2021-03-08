<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    protected $table = 'banks';
    protected $guarded = [];
    protected $appends = [
        'image_physical_path',
        'group_name'
    ];

    
    public function getImagePhysicalPathAttribute(){
        $return = NULL;
        if($this->icon !== NULL){
            $url_path = url('/').'/storage/icon/';
            $filename = str_replace($url_path,'',$this->icon); 
            $return = 'public/icon/'.$filename;
        }
        return $return;
    }

    public function getGroupNameAttribute(){
        return str_slug($this->type,'_');
    }
}
