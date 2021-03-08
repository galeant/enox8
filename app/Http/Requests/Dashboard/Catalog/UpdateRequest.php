<?php

namespace App\Http\Requests\Dashboard\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\V1\Product;

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
        $user = $this->user();
        $rules = [
            'name' => 'required|min:1|product_slug_available:' . $this->code . ',' . $user->store_id . ',' . $this->route('id'),
            'main_image_url' => 'bail|required|is_base64_or_url',
            'description' => 'required',
            'category' => 'bail|required|exists:category,id|is_child',
            'type' => 'bail|required|array|exist_or_double|publish_exist',
            'type.*.default' => 'boolean',
            'images' => 'array',
            'images.*' => 'is_base64_or_url',
            'code' => 'required',
            'featured' => 'boolean',
            'type.*.name' => 'required',
            'type.*.price' => 'required',
            'type.*.stock' => 'required',
            'status' => 'in:draft,publish',
            'tag' => 'array'
        ];


        if ($user->can_access_super_admin && $user->can_access_admin == false) {
            $rules['store_id'] = 'required';
        }

        foreach ($this->input('type', []) as $index => $type) {
            $rules['type.' . $index . '.status'] = 'nullable|in:draft,publish|publish_for_default:' . $this->type[$index]['default'];
            $rules['type.' . $index . '.image'] = 'required|is_base64_or_url';
            if (
                isset($this->type[$index]['discount_value']) ||
                isset($this->type[$index]['discount_unit']) ||
                isset($this->type[$index]['discount_effective_start_date']) ||
                isset($this->type[$index]['discount_effective_end_date'])
            ) {

                $rules['type.' . $index . '.discount_unit'] = 'required|in:decimal,percentage';
                $rules['type.' . $index . '.discount_value'] = 'required|numeric|max:' . $this->type[$index]['price'];
                if (isset($this->type[$index]['discount_unit']) && $this->type[$index]['discount_unit'] === 'percentage') {
                    $rules['type.' . $index . '.discount_value'] = 'required|numeric|max:100';
                }

                $rules['type.' . $index . '.discount_effective_start_date'] = 'required|date';
                $rules['type.' . $index . '.discount_effective_end_date'] = 'required|date|after:type.*.discount_effective_start_date';
            }
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'type.exist_or_double' => 'Default type harus di pilih satu',
            'product_slug_available' => 'Kombinasi nama dan kode sudah di gunakan',
            'is_child' => 'Hanya category anak dan cucu yang bisa di pilih',
            'publish_for_default' => 'Type yang menjadi default harus berstatus publish',
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.',
            'is_base64' => 'field :attribute bukan base64 string.',
            'is_base64_or_url' => 'field :attribute bukan base64 atau image url tidak di temukan.',
            'exist_or_double' => 'field :attribute tidak mempunyai attribute default atau memiliki lebih dari 1 attribute default',
            'product_slug_available' => 'Kombinasi nama dan kode sudah di gunakan'
        ];
    }
}
