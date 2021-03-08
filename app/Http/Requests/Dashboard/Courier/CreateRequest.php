<?php

namespace App\Http\Requests\Dashboard\Courier;

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
            'type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.'
        ];
    }
}
