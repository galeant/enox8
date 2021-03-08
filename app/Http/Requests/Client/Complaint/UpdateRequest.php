<?php

namespace App\Http\Requests\Client\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Route;
use App\Models\V1\Complaint;

class UpdateRequest extends FormRequest
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
            'id' => 'required|exists:complaint,id',
            'evidence' => 'required'
        ];
        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
