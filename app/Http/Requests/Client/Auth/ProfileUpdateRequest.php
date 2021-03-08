<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
        $user = auth()->user();
        $rules = [
            'avatar' => 'nullable|is_base64_or_url',
            'firstname' => 'min:1',
            'lastname' => 'min:6',
            'email' => [
                'email',
                Rule::unique('users')->ignore($user->email, 'email')->where(function ($q) {
                    return $q->where('deleted_at',NULL);
                })
            ],
            'phone' => 'min:1|max:14|unique:user_detail,phone,'.$user->phone.',phone',
            'phone' => [
                'min:1',
                'max:14',
                Rule::unique('user_detail')->ignore($user->detail->phone, 'phone')
            ],
            'gender' => 'in:m,f',
            'birthdate' => 'date|before:today'
        ];
        
        return $rules;
    }
    
    public function messages()
    {
        return [
            'email.required' => 'Kolom email harus di isi',
            'password.required'  => 'Kolom password harus di isi'
        ];
    }
}
