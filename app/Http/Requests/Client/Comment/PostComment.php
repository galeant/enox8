<?php

namespace App\Http\Requests\Client\Comment;

use Illuminate\Foundation\Http\FormRequest;

class PostComment extends FormRequest
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
            'product_slug' => 'required|exists:product,slug',
            'comment' => 'required',
            'comment_id' => 'bail|numeric|exists:comment,id,parent_id,0'
        ];
        return $rules;
    }
}
