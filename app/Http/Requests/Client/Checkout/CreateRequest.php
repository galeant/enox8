<?php

namespace App\Http\Requests\Client\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();
        $return = [
            'address_id' => 'bail|required|belongs_user:' . $user->id . ',address',
            'cart_id' => 'bail|array|required',
            'cart_id.*' => 'cart_user:' . $user->id,
            'bank_account_number' => 'required',
            'bank_account_name' => 'required',
            'bank_id' => 'required|exists:banks,id',
            'courier_code' => 'required|exists:courier,code'
        ];
        if ($this->voucher_code !== NULL) {
            $return['voucher_code'] = 'bail|valid_voucher:' . implode(',', $this->cart_id) . '|voucher_min_payment:' . implode(',', $this->cart_id);
        }
        return $return;
    }
    public function messages()
    {
        return [
            'cart_user' => 'Cart id :value bukan milik user yang sedang login',
            'exists' => ':attribute tidak di temukan',
            'belongs_user' => 'Address id bukan milik user yang sedang login',
            'cart_not_update' => 'Cart id :value terjadi perubahan, harap add to cart ulang',
            'exist' => 'Id yang di pilih tidak ada',
            'valid_voucher' => 'Voucher sudah tidak valid',
            'voucher_min_payment' => 'Minimum pembayaran tidak mencukupi'
        ];
    }
}
