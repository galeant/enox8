<?php

namespace App\Http\Requests\Client\Cart;

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
        return [
            'product_id' => 'bail|required|exists:product,id|product_store_active',
            'type_id' => 'bail|required|type_in_product:'.$this->product_id,
            'qty' => 'required|stock_available:'.$this->type_id
        ];
    }
    public function messages(){
        return [
            'required' => 'Field :attribute must be filled',
            'type_in_product' => 'Type id not in product',
            'stock_available' => 'Qty more than stock',
            'product_store_active' => 'Tidak bisa memilih product, tokonya tidak tersedia'
        ];
    }
}
