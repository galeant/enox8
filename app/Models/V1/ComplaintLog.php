<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ComplaintLog extends Model
{
    protected $table = 'complaint_log';

    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo('App\Models\V1\Complaint', 'complaint_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\V1\ComplaintStatus', 'status_id', 'id');
    }
}
