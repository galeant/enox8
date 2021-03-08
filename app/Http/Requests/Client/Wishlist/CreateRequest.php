<?php

namespace App\Http\Requests\Client\Wishlist;

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
            'product_id' => 'required|exists:product,id,deleted_at,NULL',
            'type_id' => 'bail|required|type_in_product:'.$this->product_id,
            // 'product_id' => 'array',
            // 'product_id.*' => 'required|exists:product,id,deleted_at,NULL'
        ];
    }
}
