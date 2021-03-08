<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TransactionStatus extends Model
{
    protected $table = 'transaction_status';
    protected $guarded = [];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function transaction(){
        return $this->hasMany('App\Models\V1\Transaction', 'status_id','id');
    }
}
