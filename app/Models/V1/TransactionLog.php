<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{

    protected $table = 'transaction_log';
    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo('App\Models\V1\Transaction', 'transaction_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\V1\TransactionStatus', 'status_id', 'id');
    }
}
