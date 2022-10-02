<?php

namespace  App\Http\Requests;


use App\Traits\CustomMessageRequest;
use App\Traits\FailedValidationRequest;
use Illuminate\Foundation\Http\FormRequest;

class CustomFormRequest extends FormRequest {
    use CustomMessageRequest;
    use FailedValidationRequest;
}
