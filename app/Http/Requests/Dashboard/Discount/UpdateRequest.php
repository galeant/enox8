<?php

namespace App\Http\Requests\Dashboard\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Route;

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
        $id = Route::input('id');
        $rules = [
            'name' => 'required|unique:discount,name,'.$id.',id',
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'value' => 'required|numeric',
            'unit' => 'bail|required|in:decimal,percentage',
            'banner' => 'required|is_base64_or_url',
            'product' => 'bail|array',
            'product.*' => 'exists:product,id',
            'category' => 'bail|array',
            'category.*' => 'exists:category,id',
            'id' => 'required|exists:discount,id',
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
            'category_exist' => 'Category with id :input not found',
            'banner.is_base64_or_url' => 'Banner harus berupa base64 atau url aktif'
        ];
        return $message;
    }

    public function all($keys = null){
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
