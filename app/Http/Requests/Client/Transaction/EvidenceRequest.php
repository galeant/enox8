<?php

namespace App\Http\Requests\Client\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Route;

class EvidenceRequest extends FormRequest
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
            'evidence' => 'required|is_base64_or_url',
            'transaction_code' => 'required|transaction_belongs:1,5|is_manual_transfer' //1 adalah status_id transaction yang ingin di cari ya itu status waiting payment
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'required'  => 'field :attribute harus di isi.',
            'is_base64_or_url' => 'field :attribute bukan base64 atau url',
            'transaction_belongs' => 'Transaction tidak di temukan',
            'is_manual_transfer' => 'Tipe pembayaran transaksi bukan manual, tidak bisa mengupload bukti bayar'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['transaction_code'] = $this->route('transaction_code');
        return $data;
    }
}
