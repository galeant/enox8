<?php

namespace App\Http\Requests\Dashboard\Promo;

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
        $user = auth()->user();
        $id = Route::input('id');
        $rules = [
            'name' => 'required|min:1|unique_slug_name:promo,' . $user->store_id,
            'description' => 'required',
            'image' => 'required|is_base64_or_url',
            'discount' => 'array',
            'discount.*' => 'is_publish:discount,true',
            'voucher' => 'array',
            'voucher.*' => 'is_publish:voucher,true',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
        if ($id !== NULL) {
            $rules['name'] = 'required|min:1|unique:promo,name,' . $id . ',id';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute harus di isi',
            'name.unique' => ':attriute sudah di gunakan',
            'is_publish'  => ':attribute harus berstatus publish',
            'is_base64_or_url' => 'field :attribute bukan base64 atau url.',
            'end_date.after' => 'End date harus lebih dari start date',
            'unique_slug_name' => 'Nama sudah di gunakan'
        ];
    }
}
