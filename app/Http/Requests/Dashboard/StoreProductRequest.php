<?php

namespace App\Http\Requests\Dashboard;

use App\Http\Requests\CustomFormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class StoreProductRequest extends CustomFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:products'],
            'description' => ['nullable'],
            'cost' => ['required'],
            'price' => ['required'],
            'stock' => ['required', 'integer'],
            'category_id' => ['required'],
            'have_iva' =>['required'],
            'have_ice' =>['required'],

            'elements' => 'nullable|array',
            'elements.*.id' => 'required|numeric',
            'elements.*.type' => 'nullable|in:PRIMARY,SECONDARY,OTHER',

            'tags' => 'nullable|array',
            'tags.*.id' => 'required|numeric',
            'tags.*.color' => 'nullable',
        ];
    }
}
