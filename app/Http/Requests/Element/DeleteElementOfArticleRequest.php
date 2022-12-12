<?php

namespace App\Http\Requests\Element;

use App\Http\Requests\CustomFormRequest;
use Illuminate\Validation\Rule;

class DeleteElementOfArticleRequest extends CustomFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => ['required','array','exists:products'],
        ];
    }
}
