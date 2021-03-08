<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'gender' => 'required|in:m,f',
            'birthdate' => 'required|date|before:today',
            'firstname' => 'required|min:1',
            'lastname' => 'required|min:1',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|min:1|max:14|unique:user_detail,phone,NULL,id',
            'password' => 'required|min:6',
            'password_confirmation' => 'bail|required|same:password'
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'email.unique' => 'User with email already exist, please login to your account',
            'phone.unique' => 'User with phone already exist, please login to your account',
            'required' => 'Field :attribute must be filled',
            'password_confirmation.same' => ':attribute must be same with password'
        ];
    }
}
