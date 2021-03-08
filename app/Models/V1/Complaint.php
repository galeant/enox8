<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaint';
    protected $guarded = [];

    // public function detail()
    // {
    //     return $this->hasMany('App\Models\V1\ComplaintDetail', 'complaint_id', 'id');
    // }

    public function status()
    {
        return $this->belongsTo('App\Models\V1\ComplaintStatus', 'status_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\V1\Transaction', 'transaction_id', 'id');
    }

    public function transactionDetail()
    {
        return $this->belongsTo('App\Models\V1\TransactionDetail', 'transaction_detail_id', 'id');
    }

    public function log()
    {
        return $this->hasMany('App\Models\V1\ComplaintLog', 'complaint_id', 'id');
    }

    public function transactionReturn()
    {
        return $this->belongsTo('App\Models\V1\Transaction', 'return_transaction_id', 'id');
    }

    public function getComplaintEvidenceAttribute($v)
    {
        if ($v !== NULL) {
            return json_decode($v);
        }
        return [];
    }
}
