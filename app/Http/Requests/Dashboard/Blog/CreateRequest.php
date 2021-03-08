<?php

namespace App\Http\Requests\Dashboard\Blog;

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
        $rule = [
            'title' => 'required|min:1|unique:blog,title',
            'content' => 'required|min:1',
            'tag' => 'array',
            'category' => 'array',
            'banner' => 'required|is_base64_or_url',
            'short_content' => 'required|min:1'
        ];
        if ($id !== NULL) {
            $rule['title'] = 'required|min:1|unique:blog,title,' . $id . ',id';
        }
        return $rule;
    }

    public function messages()
    {
        return [
            'required' => 'field :attribute must be filled',
            'array' => 'field :attribute must be array',
            'is_base64_or_url' => 'field :attribute bukan base64 atau url.'
        ];
    }
}
