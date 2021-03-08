<?php

namespace App\Http\Requests\Client\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'email' => [
                'required',
                'min:1',
                Rule::exists('users')->where(function ($query) {
                    $query->where('deleted_at', NULL)
                            ->where('can_access_customer',true);
                }),
            ]
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
