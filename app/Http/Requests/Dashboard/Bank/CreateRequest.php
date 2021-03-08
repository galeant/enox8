<?php

namespace App\Http\Requests\Dashboard\Bank;

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
        return [
            'name' => 'required|max:199',
            'account_number' => 'required|required|max:199',
            'type' => 'required',
            'image' => 'required|is_base64_or_url',
        ];
    }

    public function messages()
    {
        return [
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.',
            'is_base64_or_url' => 'field :attribute bukan base64 atau url.',
        ];
    }

}
