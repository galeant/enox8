<?php

namespace App\Http\Requests\Dashboard\Banner;

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
        $user = auth()->user();
        $id = $this->route('id');
        $rules = [
            'name' => 'required|unique_slug_name:banner,' . $user->store_id,
            'description' => 'required',
            'banner_web' => 'required|is_base64_or_url',
            'banner_mobile' => 'required|is_base64_or_url',
            'status' => 'required|in:draft,publish',
            'type' => 'required|in:category,product,voucher,redirect',
            'id' => 'array'
        ];
        if ($this->type !== 'redirect') {
            $rules['id'] = 'required|array';
        } else {
            $rules['url'] = 'required';
        }
        switch ($this->type) {
            case 'category':
                $rules['id.*'] = 'required|exists:category,id,deleted_at,NULL';
                break;
            case 'product':
                $rules['id.*'] = 'required|exists:product,id,deleted_at,NULL';
                break;
            case 'voucher':
                $rules['id.*'] = 'required|exists:voucher,id,deleted_at,NULL';
                break;
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
