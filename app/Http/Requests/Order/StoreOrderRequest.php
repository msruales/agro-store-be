<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\CustomFormRequest;

class StoreOrderRequest extends CustomFormRequest
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
            'total' => 'required',

            'details' => 'required|array',
            'details.*.product_id' => 'required',
            'details.*.quantity' => 'required',
            'details.*.cost' => 'required',
        ];
    }
}
