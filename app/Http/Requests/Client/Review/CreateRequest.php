<?php

namespace App\Http\Requests\Client\Review;

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
        $user = auth()->user();
        $id = Route::input('id');
        $rules = [
            'transaction_code' => 'required|exists:transaction,transaction_code|transaction_belongs:8|attemp_try:1,review,' . $user->id . ',' . $this->detail_id,
            'detail_id' => 'required|detail_transaction:' . $this->transaction_code,
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable',
            'image' => 'array'
        ];
        if ($id !== NULL) {
            $rules = [
                'id' => 'required|exists:review,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review' => 'nullable',
                'image' => 'array'
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'attemp_try' => 'Tidak bisa melakukan action lebih dari 1 kali'
        ];
    }
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
