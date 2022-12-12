<?php

namespace App\Http\Requests\ProductElement;

use App\Http\Requests\CustomFormRequest;

class StoreProductElementRequest extends CustomFormRequest
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
        return [
            'product_id' => ['required', 'exists:products,id'],
            'element_id' => ['required', 'exists:elements,id'],
            'type' => 'nullable|in:PRIMARY,SECONDARY,OTHER',
        ];
    }
}
