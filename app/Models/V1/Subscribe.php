<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Subscribe extends Model
{
    protected $table = "subscribe";
    protected $fillable = [
        'email'
    ];

    public $timestamps = false;

}
