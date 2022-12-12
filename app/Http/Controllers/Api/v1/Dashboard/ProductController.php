<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreProductRequest;
use App\Http\Requests\Dashboard\UpdateProductRequest;
use App\Http\Requests\Product\StoreProductTagRequest;
use App\Http\Requests\Product\StoreProductElementRequest;
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

        $products = Product::with('category', 'elements')
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
            ->with('category', 'elements')
            ->orWhereHas('tags', function (Builder $query) use ($search) {
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
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $product = Product::create($data_validated);

        if (isset($data_validated['elements'])) {
            $array_elements = [];

            foreach ($data_validated['elements'] as $element) {
                $array_elements[$element['id']] = ['type' => $element['type']];
            }
            $product->elements()->sync($array_elements);
        }

        if (isset($data_validated['tags'])) {
            $array_tags = [];

            foreach ($data_validated['tags'] as $element) {
                $array_tags[] = $element['id'];
            }
            $product->tags()->sync($array_tags);
        }

        $product->refresh();
        $product->load('category', 'elements', 'tags');

        return response()->json([
            'message' => 'ok',
            'product' => $product,
            'test' => $array_elements
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
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $product->update($data_validated);

        if (isset($data_validated['elements'])) {
            $array_elements = [];

            foreach ($data_validated['elements'] as $element) {
                $array_elements[$element['id']] = ['type' => $element['type']];
            }
            $product->elements()->sync($array_elements);
        }

        if (isset($data_validated['tags'])) {
            $array_tags = [];

            foreach ($data_validated['tags'] as $element) {
                $array_tags[] = $element['id'];
            }
            $product->tags()->sync($array_tags);
        }

        $product->load('category', 'elements', 'tags');

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

    public function store_element(StoreProductElementRequest $request, Product $product): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $product->elements()->attach($data['id']);

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

    public function showElementsByProduct(Product $product): \Illuminate\Http\JsonResponse
    {
//        ->having('product_id', '!=', $product->id)
        $elements_withCount = $product->elements()->withCount([
            'products' => function ($query) use ($product) {
                $query->where('product_id', '!=', $product->id);
            },
        ])->get();
        return $this->successResponse($elements_withCount);
    }
}
