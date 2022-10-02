<?php

namespace App\Traits;

trait CustomMessageRequest {

    public function messages(): array
    {
        return [
            'required' => 'required',
            'unique' => 'already_used',
            'exists' => 'no_exist',
            'array' => 'must_be_an_array',
            'numeric' => 'must_be_a_number',
            'in' => 'is_invalid'
        ];
    }

}

