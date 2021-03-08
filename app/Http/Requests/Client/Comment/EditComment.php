<?php

namespace App\Http\Requests\Client\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Route;

class EditComment extends FormRequest
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
            'id' => 'required|exists:comment,id|belongs_user:'.auth()->user()->id.',comment',
            'comment' => 'required'
        ];
        return $rules;
    }

    public function all($keys = null){
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
