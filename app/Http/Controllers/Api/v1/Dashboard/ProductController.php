<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreProductRequest;
use App\Http\Requests\Dashboard\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {


        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $products = Product::with('category')
            ->where('name', 'LIKE', "%$search%")
            ->paginate($per_page);


        $pagination = $this->parsePaginationJson($products);


        return $this->successResponse([
            'pagination' => $pagination,
            'products' => $products->items()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Dashboard\StoreProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {

        $product = Product::create($request->validated());
        $product->category;
        return response()->json([
            'message' => 'ok',
            'product' => $product
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        $product->category;

        return response()->json([
            'message' => 'okasd',
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Dashboard\UpdateProductRequest $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product)
    {

        $product->update($request->validated());

        return response()->json([
            'message' => 'ok',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        if (!$product->delete()) {
            return response()->json([
                'message' => 'fail',
            ]);
        }
        return response()->json([
            'message' => 'ok',
        ]);
    }
}
