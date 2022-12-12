<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UpdateProductRequest extends FormRequest
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

    function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'description' => ['nullable'],
            'price' => ['required'],
            'cost' => ['required'],
            'stock' => ['required', 'integer'],
            'category_id' => ['required'],

            'elements' => 'nullable|array',
            'elements.*.id' => 'required|numeric',
            'elements.*.type' => 'nullable|in:PRIMARY,SECONDARY,OTHER',

            'tags' => 'nullable|array',
            'tags.*.id' => 'required|numeric',
            'tags.*.color' => 'nullable',
        ];
    }
}
