<?php

namespace App\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;
use Route;
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
        $id = Route::input('id');
        $rules = [
            'name' => 'required|min:1|unique:role',
            'permission' => 'bail|required|array',
            'permission.*' => 'exists:permission,id'
        ];
        if($id !== NULL){
            $rules['name'] = 'required|min:1|unique:role,name,'.$id.',id';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.'
        ];
    }
}
