<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ComplaintDetail extends Model
{
    protected $table = 'complaint_detail';
    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo('App\Models\V1\Complaint', 'complain_id', 'id');
    }

    public function transactionDetail()
    {
        return $this->belongsTo('App\Models\V1\TransactionDetail', 'transaction_detail_id', 'id');
    }
}
