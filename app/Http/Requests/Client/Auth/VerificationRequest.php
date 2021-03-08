<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
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
            'token' => 'bail|required|exists:users,activation_token|token_valid'
        ];
    }

    public function messages(){
        return [
            'required' => 'Field :attribute must be filled',
            'token_valid' => 'Token has expired, please re-send activation email'
        ];
    }
}
