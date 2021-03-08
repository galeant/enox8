<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $table = 'transaction';
    protected $guarded = [];

    public function detail()
    {
        return $this->hasMany('App\Models\V1\TransactionDetail', 'transaction_id', 'id');
    }

    public function log()
    {
        return $this->hasMany('App\Models\V1\TransactionLog', 'transaction_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\V1\Store', 'store_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\V1\TransactionStatus', 'status_id', 'id');
    }

    public function courier()
    {
        return $this->belongsTo('App\Models\V1\Courier', 'courier_id', 'id');
    }

    public function voucherUsage()
    {
        return $this->belongsTo('App\Models\V1\VoucherUsage', 'voucher_usage_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\V1\Bank', 'bank_id', 'id');
    }

    public function complaint()
    {
        return $this->hasMany('App\Models\V1\Complaint', 'transaction_id', 'id');
    }

    public function review()
    {
        return $this->hasMany('App\Models\V1\Review', 'transaction_id', 'id');
    }
}
