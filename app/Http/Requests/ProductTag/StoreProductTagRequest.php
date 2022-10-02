<?php

namespace App\Http\Requests\ProductTag;

use App\Http\Requests\CustomFormRequest;

class StoreProductTagRequest extends CustomFormRequest
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
            'product_id' => ['required','exists:products,id'],
            'tag_id' => ['required','exists:tags,id'],
        ];
    }
}
