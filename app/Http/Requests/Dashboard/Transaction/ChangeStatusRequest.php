<?php

namespace App\Http\Requests\Dashboard\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Route;
use App\Models\V1\Transaction;

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
        $id = Route::input('id');
        $status = Route::input('status');
        $user = auth()->user();
        $transaction = Transaction::where('store_id', $user->store_id)->where('id', $id)->firstOrFail();
        if ($transaction->status_id === 4 && $transaction->bank_account_type === 'Transfer Bank (Verifikasi Manual)' && $status === 'decline') {
            return [
                'payment_return_evidence' => 'required|is_base64_or_url'
            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'required'  => 'The :attribute field is Required.'
        ];
    }
}
