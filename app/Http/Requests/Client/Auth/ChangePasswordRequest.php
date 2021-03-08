<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'old_password' => 'bail|required|is_valid_password:'.$user->password,
            'new_password' => 'bail|required|not_in:'.$this->old_password,
            'confirm_new_password' => 'bail|required|same:new_password'
        ];
        
        return $rules;
    }
    
    public function messages()
    {
        return [
            'required'  => 'Kolom harus di isi',
            'not_in' => 'New password tidak boleh sama dengan old password',
            'same' => 'Confirm new passwor harus sama dengan new password',
            'is_valid_password' => 'Old password tidak sama dengan password yang sekarang'
        ];
    }
}
