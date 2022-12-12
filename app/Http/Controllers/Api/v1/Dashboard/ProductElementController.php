<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ProductElement\StoreProductElementRequest;
use App\Models\ProductElement;
use Illuminate\Http\Request;

class ProductElementController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function store(StoreProductElementRequest $request): \Illuminate\Http\JsonResponse
    {

        $productTag = ProductElement::create($request->validated());
        return $this->successResponse($productTag);
    }

    public function show(ProductElement $productElement)
    {
        //
    }

    public function update(Request $request, ProductElement $productElement)
    {
        //
    }

    public function destroy(ProductElement $productElement)
    {
        //
    }
}
