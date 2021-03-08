<?php

namespace App\Http\Requests\Dashboard\Comment;

use Illuminate\Foundation\Http\FormRequest;

class ReplyRequest extends FormRequest
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
            'id' => 'required|exists:comment,id,parent_id,0',
            'comment' => 'required',
        ];
        return $rules;
    }

    public function all($keys = null){
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
