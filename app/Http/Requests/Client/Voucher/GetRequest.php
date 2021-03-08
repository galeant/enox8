<?php

namespace App\Http\Requests\Client\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
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
            'cart' => 'bail|required|array',
            'cart.*.id' => 'bail|exists:shopping_cart,id|belongs_user:'.auth()->user()->id.',cart'
        ];
        return $rules;
    }
}
