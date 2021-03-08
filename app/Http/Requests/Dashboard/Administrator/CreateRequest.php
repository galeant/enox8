<?php

namespace App\Http\Requests\Dashboard\Administrator;

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
            'email' => 'required|unique:users,email',
            'role' => 'required|exists:role,id',
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required|unique:user_detail,phone',
            'avatar' => 'is_base64_or_url'
        ];
        if (isset($id)) {
            if (auth()->user()->can_access_super_admin === false && auth()->user()->can_access_admin === true) {
                $rules['id'] = 'required|exists:users,id|same_store:' . auth()->user()->store_id;
            }
            $rules['email'] = 'required|unique:users,email,' . $id . ',id';
            $rules['phone'] = 'required|unique:user_detail,phone,' . $id . ',user_id';
        }
        return $rules;
    }
    public function messages()
    {
        return [
            'required' => 'Field :attribute must be filled',
            'same_store' => 'Id not found'
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
