<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Tag\DeleteTagOfArticleRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {

        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $tags = Tag::where('name', $search)
            ->orderBy('id', 'desc')->paginate($per_page);

        $pagination = $this->parsePaginationJson($tags);

        return $this->successResponse([
            'pagination' => $pagination,
            'tags' => $tags->items()
        ]);
    }

    public function all()
    {
        $tags = Tag::all();

        return $this->successResponse($tags);
    }


    public function store(StoreTagRequest $request): \Illuminate\Http\JsonResponse
    {
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $tag = Tag::create($data_validated);
        return $this->successResponse($tag);
    }

    public function select_tags(): \Illuminate\Http\JsonResponse
    {
        $tags = Tag::select('name', 'id', 'color')->with('products')->get()->all();
        return $this->successResponse($tags);
    }

    public function select_short_tags(): \Illuminate\Http\JsonResponse
    {
        $tags = Tag::select('name', 'id', 'color')
            ->withCount('products')
            ->has('products')
            ->orderBy('products_count', 'desc')
            ->get();
        return $this->successResponse($tags);
    }

    public function tag_with_products()
    {
        $tags = Tag::select('name', 'id', 'color')->with('products')->get()->all();
    }

    public function show(Tag $tag)
    {
        //
    }

    public function update(UpdateTagRequest $request, Tag $tag): \Illuminate\Http\JsonResponse
    {
        $data_validated = $request->validated();
        $data_validated['name'] = strtoupper($data_validated['name']);

        $tag->update($data_validated);
        return $this->successResponse($tag);
    }

    public function destroy(Tag $tag): \Illuminate\Http\JsonResponse
    {
        if (!$tag->delete()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }

    public function delete_tag_of_article(DeleteTagOfArticleRequest $request, Tag $tag): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $tag->products()->detach($data['id']);

        $tag->load('products');

        $tag->refresh();

        return $this->successResponse($tag);

    }
}
