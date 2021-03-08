<?php

namespace App\Http\Requests\Dashboard\Voucher;

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
        $id = $this->route('id');
        $rules = [
            'id' => 'required|exists:voucher,id',
            'name' => 'required|min:1',
            'code' => 'required|min:1|max:255|unique:voucher,code,'.$id.',id,deleted_at,NULL',
            'description' => 'min:1',
            'effective_start_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'effective_end_date' => 'required|date|date_format:Y-m-d|after:start_date',
            'value' => 'required|numeric',
            'unit' => 'required|in:percentage,decimal',
            'image' => 'is_base64_or_url',
            'minimum_payment' => 'numeric',
            'limit_per_user' => 'numeric',
            'limit_per_user_per_day' => 'numeric',
            'status' => 'in:draft,publish'
        ];
        if($this->input('unit') === 'percentage'){
            $rules['value'] = 'required|numeric|max:100';
            $rules['max_discount'] = 'required|numeric';
        }
        return $rules;
    }
    public function messages()
    {
        $message = [
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.',
            'product_exist' => 'Product with id :input not found',
            'category_exist' => 'Category with id :input not found'
        ];
        return $message;
    }

    public function all($keys = null){
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
