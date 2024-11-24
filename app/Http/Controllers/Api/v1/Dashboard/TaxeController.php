<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Tax\StoreTaxRequest;
use App\Models\Taxe;

class TaxeController extends ApiController
{


    public function index(): \Illuminate\Http\JsonResponse
    {
        $taxes = Taxe::all();

        return $this->successResponse($taxes);
    }

    public function store(StoreTaxRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $newTax = Taxe::create($data);

        return $this->successResponse($newTax);
    }

    public function activeSelection($id): \Illuminate\Http\JsonResponse
    {
        Taxe::where('is_active', true)->update(['is_active' => false]);
        $tax = Taxe::where('id', $id)->first();
        $tax->is_active = 1;
        $tax->update();

        return $this->successResponse($tax);

    }

}
