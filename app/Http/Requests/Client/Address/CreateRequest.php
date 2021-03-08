<?php

namespace App\Http\Requests\Client\Address;

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
            'address' => 'required',
            'country_id' => 'required|exists:countries,id',
            'province_id' => 'required|exists:provinces,id',
            'regency_id' => 'required|exists:regencies,id',
            'district_id' => 'required|exists:districts,id',
            'village_id' => 'required|exists:villages,id',
            'alias' => 'required',
            'recipient_name' => 'required',
            'phone' => 'required',
            'postal_code' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'main_address' => 'required|boolean'
        ];
        if($id !== NULL){
            $rules['id'] = 'bail|exists:user_address,id|belongs_user:'.auth()->user()->id.',address';
        }
        return $rules;
    }

    public function messages(){
        return [
            'required' => 'Field :attribute must be filled'
        ];
    }
    public function all($keys = null){
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
