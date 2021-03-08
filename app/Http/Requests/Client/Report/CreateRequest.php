<?php

namespace App\Http\Requests\Client\Report;

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
        switch($this->relation_type){
            case 'user':
                $this->relation_type = 'users';
                break;
            default:
            $this->relation_type = 'comment';
        }

        $rules = [
            'relation_type' => 'nullable|in:comment,user,product,store',
            'relation_id' => 'bail|required|exists:'.$this->relation_type.',id',
            'reason' => 'required'
        ];
        return $rules;
    }
}
