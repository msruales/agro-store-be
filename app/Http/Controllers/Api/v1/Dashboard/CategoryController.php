<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreCategoryRequest;
use App\Http\Requests\Dashboard\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $categories = Category::with('products')
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

        $pagination = $this->parsePaginationJson($categories);

        return $this->successResponse([
            'pagination' => $pagination,
            'categories' => $categories->items()
        ]);
    }


    public function store(StoreCategoryRequest $request): \Illuminate\Http\JsonResponse
    {

        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);
    }


    public function show(Category $category): \Illuminate\Http\JsonResponse
    {

        $category->image;

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);
    }

    public function getAllCategories(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::orderBy('id', 'desc')->get();

        return $this->successResponse([
            'categories' => $categories
        ]);
    }

    public function store_image(Category $category)
    {


        if ($category->image) {

            $category->image->url = 'nuevotest';

            $category->image->update();

            return $this->successResponse($category);
        }

        $image = new Image;

        $image->url = 'testUrl';

        $category->image()->save($image);

        $category->image;

        return $this->successResponse($category);

    }

    public function update(UpdateCategoryRequest $request, Category $category): \Illuminate\Http\JsonResponse
    {

        $category->update($request->validated());

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);

    }


    public function destroy(Category $category): \Illuminate\Http\JsonResponse
    {
        if (!$category->delete()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }

    public function restore($id): \Illuminate\Http\JsonResponse
    {
        $category = Category::withTrashed()->findOrFail($id);

        if (!$category->restore()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }
}
