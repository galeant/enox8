<?php

namespace App\Http\Requests\Dashboard\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusProductRequest extends FormRequest
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
            'id' => 'required|array',
            'id.*' => 'exists:product,id,deleted_at,NULL',
            'status' => 'required|in:draft,publish'
        ];
        return $rules;
    }
}
