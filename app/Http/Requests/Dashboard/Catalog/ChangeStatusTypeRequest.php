<?php

namespace App\Http\Requests\Dashboard\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusTypeRequest extends FormRequest
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
            'product_id' => 'required|exists:product,id',
            'type_id' => 'required|array',
            'type_id.*' => 'exists:product_type,id,product_id,'.$this->product_id,
            'status' => 'required|in:draft,publish'
        ];
        return $rules;
    }
}
