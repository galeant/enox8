<?php

namespace App\Http\Requests\Dashboard\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeStatusRequest extends FormRequest
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
            'status' => 'required|in:draft,publish',
            'id' => 'required|array',
            'id.*' => 'exists:discount,id,deleted_at,NULL'
        ];

        return $rules;
    }

    public function messages()
    {
        $message = [
        ];
        return $message;
    }
}
