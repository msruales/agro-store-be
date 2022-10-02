<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreProductRequest;
use App\Http\Requests\Dashboard\UpdateProductRequest;
use App\Http\Requests\Product\StoreProductTagRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {

        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $products = Product::with('category')
            ->when($status === 'active', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orderBy('id', 'desc');
            })
            ->when($status === 'all', function ($query) use ($search) {
                $query->withTrashed()->where('name', 'LIKE', "%$search%")
                    ->orderBy('id', 'desc');
            })
            ->when($status === 'deleted', function ($query) use ($search) {
                $query->onlyTrashed()->where('name', 'LIKE', "%$search%")
                    ->orderBy('id', 'desc');
            })
            ->paginate($per_page);


        $pagination = $this->parsePaginationJson($products);


        return $this->successResponse([
            'pagination' => $pagination,
            'products' => $products->items()
        ]);
    }

    public function select(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->query('search') ? $request->query('search') : '';

        $products = Product::where('name', 'LIKE', "%$search%")
            ->with('category')
            ->orWhereHas('tags', function (Builder $query) use($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->take(8)
            ->get();

        return $this->successResponse([
            'products' => $products
        ]);
    }

    public function store(StoreProductRequest $request): \Illuminate\Http\JsonResponse
    {

        $product = Product::create($request->validated());
        $product->refresh();
        $product->load('category');

        return response()->json([
            'message' => 'ok',
            'product' => $product
        ]);

    }

    public function show(Product $product): \Illuminate\Http\JsonResponse
    {
        $product->load('category');

        return response()->json([
            'message' => 'okasd',
            'product' => $product
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): \Illuminate\Http\JsonResponse
    {

        $product->update($request->validated());
        $product->load('category');

        return response()->json([
            'message' => 'ok',
            'product' => $product
        ]);
    }

    public function destroy(Product $product): \Illuminate\Http\JsonResponse
    {
        if (!$product->delete()) {
            return $this->errorResponse();
        }
        return $this->successResponse();
    }

    public function restore($id): \Illuminate\Http\JsonResponse
    {
        $category = Product::withTrashed()->findOrFail($id);

        if (!$category->restore()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }

    public function store_tag(StoreProductTagRequest $request, Product $product): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $product->tags()->attach($data['id']);

        $product->refresh();

        return $this->successResponse($product);

    }

    public function delete_product_tag(StoreProductTagRequest $request, Product $product): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $product->tags()->detach($data['id']);

        $product->refresh();

        return $this->successResponse($product);

    }
}
