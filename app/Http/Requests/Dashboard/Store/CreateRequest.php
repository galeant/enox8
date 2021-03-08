<?php

namespace App\Http\Requests\Dashboard\Store;

use Illuminate\Foundation\Http\FormRequest;
// use Illuminate\Validation\Rule;

// use App\Models\V1\User;
// use App\Models\V1\Store;

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
            'firstname' => 'required|min:1',
            'lastname' => 'required|min:1',
            'email' => 'required|min:1|unique:users,email',
            'password' => 'required|min:1',
            'phone' => 'required|min:1',
            'store_name' => 'required|min:1|unique:store,name',
            'store_address' => 'required|min:1',
            'store_phone' => 'required|min:1',
            'store_email' => 'required|min:1',
            'store_regency' => 'required',
            'store_province' => 'required',
            'store_country' => 'required',
            'store_district' => 'required',
            'store_village' => 'required',
            'store_postal_code' => 'required'
        ];
        // $user = User::where('email',$this->user()->email)->first();
        // if($user !== NULL){
        //     $rules['email'] = 'required|min:1|unique:users,email,'.$user->id;
        // }
        // $store = Store::where('name',$this->store_name)->first();
        // if($store !== NULL){
        //     $rules['email'] = 'required|min:1|unique:store,name,'.$store->id;
        // }
        return $rules;
    }

    public function messages()
    {
        return [
            'required'  => 'field :attribute harus di isi.',
            'min' => 'field :attribute minimal mempunya 1 karakter',
            'unique' => 'field :attribute sudah ada yang menggunakan'
        ];
    }
}
