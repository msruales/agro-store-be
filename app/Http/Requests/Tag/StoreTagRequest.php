<?php

namespace App\Http\Requests\Tag;

use App\Http\Requests\CustomFormRequest;

class StoreTagRequest extends CustomFormRequest
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
            'name' => ['required','unique:tags'],
            'color' => ['required'],
        ];
    }
}
