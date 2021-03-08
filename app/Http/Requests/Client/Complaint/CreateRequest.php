<?php

namespace App\Http\Requests\Client\Complaint;

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
        $rules = [
            'transaction_code' => 'bail|required|transaction_belongs:7',
            'transaction_detail_id' => 'required|detail_to_transaction:' . $this->transaction_code . '|complained',
            'qty' => 'bail|required|qty_limit:' . $this->transaction_code . ',' . $this->transaction_detail_id,
            'complaint' => 'required',
            'evidence' => 'required|array'
        ];
        return $rules;
    }

    public function messages()
    {
        $messages = [
            'required' => ':attribute harus di isi',
            'transaction_belongs' => 'Transaksi status bukan complete',
            'detail_to_transaction' => 'Transaksi detail bukan milik transaksinya',
            'complained' => 'Transaksi detail sudah pernah di complaint',
            'array' => ':attribute harus berbentuk array',
            'qty_limit' => 'tidak boleh melebihi pembelian'
        ];
        // foreach ($this->input('complaint_item', []) as $key => $value) {
        //     $messages['complaint_item.' . $key . '.detail_to_transaction'] = 'complaint_item ke-' . $key . '  bukan detail dari transaksi';
        //     $messages['complaint_item.' . $key . '.qty_limit'] = 'complaint_item ke-' . $key . '  tidak bisa melebihi pembelian';
        // }
        return $messages;
    }
}
