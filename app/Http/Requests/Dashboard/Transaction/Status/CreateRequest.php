<?php

namespace App\Http\Requests\Dashboard\Transaction\Status;

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
        $rules = [
            'name' => 'required|string|max:255|unique:transaction_status,name'
        ];
        
        $id = Route::input('id');
        if($id !== NULL){
            $rules['name'] = 'required|string|max:255|unique:transaction_status,name,'.$id.',id';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'required'  => 'The :attribute field is Required.'
        ];
    }
}
