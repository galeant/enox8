<?php

namespace App\Http\Requests\Dashboard\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => 'required|min:1',
            'password' => 'required|min:6',
            'grant_type' => 'required',
            'client_id' => 'required'
        ];
        return $rules;
    }
    
    public function messages()
    {
        return [
            'email.required' => 'Kolom email harus di isi',
            'password.required'  => 'Kolom password harus di isi',
            'grant_type.required'  => 'Kolom grant_type harus di isi',
            'client_id.required'  => 'Kolom client_id harus di isi'
            // 'client_secret.required'  => 'The Client Secret field is Required.'
        ];
    }


}
