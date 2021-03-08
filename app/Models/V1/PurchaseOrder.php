<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use softDeletes;
    protected $table = 'purchase_order';

    protected $fillable = [
        'po_number',
        'admin_id',
        'total_qty',
        'kurs',
        'total_payment',
        'delivery_date',
        'total_tax',
        'delivery_expense',
        'recipient'
    ];

    public function item(){
        return $this->hasMany('App\Models\V1\PurchaseOrderItem','purchase_order_id','id');
    }

    public function getStatusAttribute($val){
        switch($val){
            case 0:
                $return = 'Not validate';
            break;
            case 1:
                $return = 'Already validate';
            break;
            default:
                $return = 'Non status';
        }
        return $return;
    }
}
