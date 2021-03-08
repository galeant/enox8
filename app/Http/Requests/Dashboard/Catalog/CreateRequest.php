<?php

namespace App\Http\Requests\Dashboard\Catalog;

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
        $user = $this->user();
        $rules = [
            'name' => 'required|min:1|unique:product,name|product_slug_available:' . $this->code . ',' . $user->store_id,
            'code' => 'required',
            'main_image_url' => 'required|is_base64_or_url',
            'description' => 'required',
            'category' => 'bail|required|exists:category,id|is_child',
            'type' => 'bail|required|array|exist_or_double',
            'images' => 'array',
            'images.*' => 'is_base64_or_url',
            'type.*.price' => 'required',
            'type.*.stock' => 'required',
            'type.*.default' => 'boolean',
            'featured' => 'boolean',
            'status' => 'nullable|in:draft,publish',
            'tag' => 'array',
            'tag.*' => 'required|string'
        ];

        if ($user->can_access_super_admin && $user->store_id === NULL) {
            $rules['store_id'] = 'required';
            $rules['name'] = 'required|min:1|unique:product,name|product_slug_available:' . $this->code . ',' . $this->store_id;
        }

        foreach ($this->input('type', []) as $index => $type) {
            $rules['type.' . $index . '.status'] = 'required|in:draft,publish|publish_for_default:' . $this->type[$index]['default'];
            $rules['type.' . $index . '.image'] = 'required|is_base64_or_url';
            if (
                isset($this->type[$index]['discount_value']) ||
                isset($this->type[$index]['discount_unit']) ||
                isset($this->type[$index]['discount_effective_start_date']) ||
                isset($this->type[$index]['discount_effective_end_date'])
            ) {

                $rules['type.' . $index . '.discount_unit'] = 'required|in:decimal,percentage';
                $rules['type.' . $index . '.discount_value'] = 'required|numeric|max:' . $this->type[$index]['price'];
                if ($this->type[$index]['discount_unit'] === 'percentage') {
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
            'required'  => 'field :attribute harus di isi.',
            'array'    => 'field :attribute harus berupa array.',
            'is_base64_or_url' => 'field :attribute harus base64 atau url yang masih aktif',
            // 'category.*.exists' => 'Category dengan id :input tidak ditemukan',
            'type.exist_or_double' => 'Default type harus di pilih satu',
            'product_slug_available' => 'Kombinasi nama dan kode sudah di gunakan',
            'is_child' => 'Hanya category anak dan cucu yang bisa di pilih',
            'publish_for_default' => 'Type yang menjadi default harus berstatus publish'
        ];
    }
}
