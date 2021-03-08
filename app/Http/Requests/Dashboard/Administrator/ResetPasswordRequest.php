<?php

namespace App\Http\Requests\Dashboard\Administrator;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'admin_id' => 'bail|required|user_exist_with_explode:admin|same_store:'.auth()->user()->store_id
        ];
        return $rules;
    }
}
