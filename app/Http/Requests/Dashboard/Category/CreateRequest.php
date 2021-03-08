<?php

namespace App\Http\Requests\Dashboard\Category;

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
            'name' => 'required|unique:category,name,NULL,id,deleted_at,NULL',
            'description' => 'required',
            'parent_id' => 'bail|required|exist_except_zero:category,id|parent_recursive_counter',
            'status' => 'in:publish,draft',
            'order' => 'numeric|unique:category,order,NULL,id,deleted_at,NULL',
            'icon' => 'is_base64_or_url',
            'thumbnail' => 'is_base64_or_url',
        ];

        if ($id !== NULL) {
            $rules['parent_id'] = 'bail|required|exist_except_zero:category,id|parent_recursive_counter|not_self:' . $id;
            $rules['id'] = 'exists:category,id';
            $rules['name'] = 'required|unique:category,name,' . $id . ',id,deleted_at,NULL';
            $rules['order'] = 'required|numeric|unique:category,order,' . $id . ',id,deleted_at,NULL';
            $rules['status'] = 'required|in:publish,draft';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Kolom name harus di isi',
            'name.unique' => 'Name sudah di gunakan',
            'description.required'  => 'Kolom description harus di isi',
            'is_base64_or_url' => 'field :attribute bukan base64 atau url.',
            'parent_recursive_counter' => 'Parent id tidak memenuhi kriteria',
            'not_self' => 'Parent id tidak boleh sama dengan id yang di update'
            // 'android_icon.required'  => 'The android_icon field is Required.',
            // 'web_icon.required'  => 'The web_icon field is Required.'
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
