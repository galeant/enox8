<?php

namespace App\Http\Requests\Client\Address;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
            'address_id' => 'bail|required|array',
            'address_id.*' => 'bail|exists:user_address,id|belongs_user:'.auth()->user()->id.',address'
        ];
    }

    public function messages(){
        return [
            'required' => 'Field :attribute must be filled',
            'array' => 'Field :attribute must be array',
            'exists' => 'Address id not found',
            'belongs_user' => 'Address id not belongs to user'
        ];
    }
}
