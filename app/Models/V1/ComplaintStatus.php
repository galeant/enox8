<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ComplaintStatus extends Model
{
    protected $table = 'complaint_status';
    protected $guarded = [];

    public function complaint()
    {
        return $this->hasMany('App\Models\V1\Complaint', 'status_id', 'id');
    }
}
