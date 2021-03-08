<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $table = 'voucher_usage';
    protected $guarded = [];

    // protected $appends = [
    //     'voucher_discount_format',
    //     'total_payment_format',
    // ];

    public function user()
    {
        return $this->belongsTo('App\Models\V1\User', 'user_id', 'id');
    }

    public function voucher()
    {
        return $this->belongsTo('App\Models\V1\Voucher', 'voucher_code', 'code');
    }

    public function transaction()
    {
        return $this->hasMany('App\Models\V1\Transaction', 'voucher_usage_id', 'id');
    }

    // public function getVoucherDiscountFormatAttribute(){
    //     return number_format((float)$this->voucher_discount_value, 2, '.', '');
    // }

    // public function getTotalPaymentFormatAttribute($v){
    //     return number_format((float)$this->total_payment, 2, '.', '');
    // }
}
