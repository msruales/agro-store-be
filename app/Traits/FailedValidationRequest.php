<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

trait FailedValidationRequest {

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }
    }

}

