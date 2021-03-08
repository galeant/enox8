<?php

namespace App\Http\Requests\Client\Cart;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
        $rules = [
            'cart_id' => 'bail|required|array',
            'cart_id.*' => 'bail|exists:shopping_cart,id|cart_user:'.$user->id
        ];
        

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
