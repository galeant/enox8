<?php

namespace App\Http\Requests\Dashboard\Voucher;

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
            'name' => 'required|min:1',
            'code' => 'required|min:1|max:255|unique:voucher,code,NULL,id,deleted_at,NULL',
            'effective_start_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'effective_end_date' => 'required|date|date_format:Y-m-d|after:start_date',
            'value' => 'required|numeric',
            'unit' => 'required|in:percentage,decimal',
            'image' => 'is_base64_or_url',
            'minimum_payment' => 'numeric',
            'limit_per_user' => 'numeric',
            'limit_per_user_per_day' => 'numeric',
            'status' => 'in:draft,publish',
            'product' => 'array',
            'product.**' => 'exists:product,id,deleted_at,NULL',
            'category' => 'array',
            'category.*' => 'exists:category,id,deleted_at,NULL'
        ];
        if ($this->input('unit') === 'percentage') {
            $rules['value'] = 'required|numeric|max:100';
            $rules['max_discount'] = 'required|numeric';
        }
        return $rules;
    }
}
