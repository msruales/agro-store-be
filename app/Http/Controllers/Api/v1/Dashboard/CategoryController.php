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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $categories = Category::with('products')
            ->where('name', 'LIKE', "%$search%")
            ->paginate($per_page);

        $pagination = $this->parsePaginationJson($categories);

        return $this->successResponse([
            'pagination' => $pagination,
            'categories' => $categories->items()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Dashboard\StoreCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request)
    {

        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category)
    {

        $category->image;

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);
    }

    public function getAllCategories(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::all();

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

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Dashboard\UpdateCategoryRequest $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {

            $category_id=$_POST['categoryId'];

        $category->update($request->validated());

        return response()->json([
            'message' => 'ok',
            'category' => $category
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        if (!$category->delete()) {
            return response()->json([
                'message' => 'fail',
            ]);
        }

        return response()->json([
            'message' => 'ok'
        ]);
    }
}
