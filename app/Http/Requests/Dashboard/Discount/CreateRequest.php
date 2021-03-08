<?php

namespace App\Http\Requests\Dashboard\Discount;

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
        $rules = [
            'name' => 'required|unique:discount,name',
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'value' => 'required|numeric',
            'unit' => 'bail|required|in:decimal,percentage',
            'banner' => 'required|is_base64_or_url',
            'product' => 'bail|array|nullable',
            'product.*' => 'is_publish:product',
            'category' => 'bail|array',
            'category.*' => 'is_publish:category',
            'display_on_client' => 'bail|nullable|boolean',
            'status' => 'required|in:draft,publish'
        ];

        if($this->input('unit') === 'percentage'){
            $rules['value'] = 'required|numeric|max:100';
        }
        return $rules;
    }

    public function messages()
    {
        $message = [
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.',
            'product_exist' => 'Product with id :input not found',
            'exists' => 'Category with id :input not found',
            'product.*.is_publish' => 'Product id tidak ada',
            'category.*.is_publish' => 'Category id tidak ada',
            'banner.is_base64_or_url' => 'Banner harus berupa base64 atau url aktif'
        ];
        return $message;
    }
}
