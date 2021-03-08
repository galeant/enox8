<?php

namespace App\Http\Requests\Dashboard\Blog;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
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
            'id.*' => 'exists:blog,id,deleted_at,NULL',
            'status' => 'required|in:draft,publish'
        ];
        return $rules;
    }
}
