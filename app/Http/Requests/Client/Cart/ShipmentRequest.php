<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
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
        $rules = [
            'cart' => 'bail|required|array'
        ];
        foreach($this->input('cart',[]) as $index => $cart){
            $rules['cart.'.$index.'.id'] = 'bail|exists:shopping_cart,id|belongs_user:'.auth()->user()->id.',cart';
            $rules['cart.'.$index.'.qty'] = 'required|numeric|stock_available:NULL,'.$this->cart[$index]['id'];
        }
        // if($this->voucher_code !== NULL){
        //     $return['voucher_code'] = 'bail|valid_voucher|voucher_min_payment:'.implode($this->cart_id,',');
        // }
        return $rules;
    }
    public function messages(){
        return [
            'required' => 'Field :attribute must be filled',
            'type_in_product' => 'Type id not in product',
            'stock_available' => 'Qty more than stock'
        ];
    }
}
