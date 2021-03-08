<?php

namespace App\Http\Requests\Client\Courier;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
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
            'cart_id' => 'bail|array|required',
            'cart_id.*' => 'cart_user:' . $user->id,
            'address_id' => 'bail|required|belongs_user:' . $user->id . ',address',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'required' => ':attribute harus di isis',
            'cart_user' => 'Cart id :value bukan milik user yang sedang login',
            'belongs_user' => 'Address id bukan milik user yang sedang login',
            'array' => ':attribute harus berbentuk array'
        ];
    }
}
