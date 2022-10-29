<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserRequest extends FormRequest
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
            'role_id' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'document_type' => "required | in:RUC,CI",
            'email' => ['required','email',Rule::unique('persons', 'email')->ignore($this->route('user')->person->id)],
            'document_number' => ['required',Rule::unique('persons', 'document_number')->ignore($this->route('user')->person->id)],
            'direction' => "required",
            'phone_number' => "required",
            'password' => 'nullable'
        ];
    }
}
