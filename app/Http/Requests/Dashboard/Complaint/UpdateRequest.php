<?php

namespace App\Http\Requests\Dashboard\Complaint;

use Illuminate\Foundation\Http\FormRequest;
use Route;
use App\Models\V1\Complaint;
use Exception;

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
        ];

        $data = Complaint::where('id', $this->id)->firstOrFail();
        switch ($data->status_id) {
            case 1;
                $rules['status'] = 'bail|required|in:accept,decline';
                break;
            case 2;
                break;
            case 3;
                break;
            case 4;
                $rules['evidence'] = 'required';
                break;
            case 5;
                break;
            case 6;
                throw new Exception('Tidak dapat melanjutkan proses, masih menunggu user mengirim balik barang');
                break;
            case 7;
                $rules['evidence'] = 'required';
                break;
            case 8;
                break;
            case 9;
                break;
        }
        if ($data->status_id && $this->status === 'accept') {
            $rules['need_return'] = 'required|boolean';
            $rules['compensate_type'] = 'required|in:cash_return,product_return';
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
