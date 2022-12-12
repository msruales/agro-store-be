<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\ProductTag\StoreProductTagRequest;
use App\Models\ProductTag;
use Illuminate\Http\Request;

class ProductTagController extends ApiController
{

    public function index()
    {
        //
    }

    public function store(StoreProductTagRequest $request)
    {

        $productTag = ProductTag::create($request->validated());
        return $this->successResponse($productTag);
    }

    public function show(ProductTag $productTag)
    {
        //
    }


    public function update(Request $request, ProductTag $productTag)
    {
        //
    }

    public function destroy(ProductTag $productTag)
    {
        if (!$productTag->delete()) {
            return $this->errorResponse();
        }
        return $this->successResponse();
    }
}
