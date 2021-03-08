<?php

namespace App\Http\Requests\Dashboard\Store;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\V1\Store;

class UpdateRequest extends FormRequest
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
            'name' => 'required|min:1|unique:store,name',
            'address' => 'required|min:1',
            'phone' => 'required|min:1',
            'email' => 'required|min:1',
            'country_id' => 'required',
            'regency_id' => 'required',
            'district_id' => 'required',
            'province_id' => 'required',
            'village_id' => 'required',
            'postal_code' => 'required',
            'logo' => 'is_base64_or_url'
        ];
        if ($user->can_access_admin && $user->store_id !== NULL) {
            $rules['name'] = 'required|min:1|unique:store,name,' . $user->store_id;
        } else if ($user->can_access_super_admin && $user->store_id === NULL) {
            $rules['id'] = 'required|exists:store,id';
        }

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
