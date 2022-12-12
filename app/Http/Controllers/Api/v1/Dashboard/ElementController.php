<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Element\DeleteElementOfArticleRequest;
use App\Http\Requests\Element\StoreElementRequest;
use App\Http\Requests\Element\UpdateElementRequest;
use App\Models\Element;
use App\Models\Product;
use Illuminate\Http\Request;

class ElementController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $elements = Element::where('name', 'LIKE', "%$search%")
            ->orderBy('id', 'desc')->paginate($per_page);

        $pagination = $this->parsePaginationJson($elements);

        return $this->successResponse([
            'pagination' => $pagination,
            'elements' => $elements->items()
        ]);
    }

    public function all()
    {
        $elements = Element::all();

        return $this->successResponse($elements);
    }

    public function showProductByElement(Element $element, Product $product): \Illuminate\Http\JsonResponse
    {
        $products = $element->products()->where('product_id','!=',$product->id)->get();
        return $this->successResponse($products);
    }


    public function store(StoreElementRequest $request): \Illuminate\Http\JsonResponse
    {
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $element = Element::create($data_validated);
        return $this->successResponse($element);
    }

    public function selectElements(): \Illuminate\Http\JsonResponse
    {
        $elements = Element::select('name', 'id')->with('products')->get()->all();
        return $this->successResponse($elements);
    }

    public function elementWithProducts()
    {
        $element = Element::select('name', 'id')->with('products')->get()->all();
    }

    public function show(Element $element)
    {
        //
    }

    public function showByProduct(Element $element)
    {
        //
    }

    public function update(UpdateElementRequest $request, Element $element): \Illuminate\Http\JsonResponse
    {
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $element->update($data_validated);
        return $this->successResponse($element);
    }

    public function destroy(Element $element): \Illuminate\Http\JsonResponse
    {
        if (!$element->delete()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }

    public function deleteElementOfArticle(DeleteElementOfArticleRequest $request, Element $element): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $element->products()->detach($data['id']);

        $element->load('products');

        $element->refresh();

        return $this->successResponse($element);

    }
}
