<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $user = $this->user();
        return [
            'cart_id' => 'bail|required|exists:shopping_cart,id|cart_user:'.$user->id,
            'qty' => 'required|stock_available:NULL,'.$this->cart_id
        ];
    }

    public function messages(){
        return [
            'required' => 'Field :attribute must be filled',
            'type_in_product' => 'Type id not in product',
            'stock_available' => 'Qty more than stock'
        ];
    }
}
