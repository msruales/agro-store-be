<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreBillRequest;
use App\Http\Requests\Dashboard\StorePersonRequest;
use App\Http\Requests\Dashboard\UpdateBillRequest;
use App\Http\Requests\Dashboard\UpdatePersonRequest;
use App\Models\Person;
use Illuminate\Http\Request;


class ClientController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $persons = Person::
        when($status === 'active', function ($query) use ($search) {
            $query->where('full_name', 'LIKE', "%$search%");
        })
            ->when($status === 'all', function ($query) use ($search) {
                $query->withTrashed()->where('full_name', 'LIKE', "%$search%");
            })
            ->when($status === 'deleted', function ($query) use ($search) {
                $query->onlyTrashed()->where('full_name', 'LIKE', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($per_page);

        $pagination = $this->parsePaginationJson($persons);

        return $this->successResponse([
            'pagination' => $pagination,
            'clients' => $persons->items()
        ]);

    }

    public function select(Request $request)
    {
        $search = $request->query('search') ? $request->query('search') : '';

        $clients = Person::select(['id', 'full_name'])->get();

        return $this->successResponse($clients);
    }

    public function store(StorePersonRequest $request): \Illuminate\Http\JsonResponse
    {

        $new_person = Person::create($request->validated());

        return $this->successResponse(
            [
                'message' => 'ok',
                'client' => $new_person
            ]
        );
    }

    public function show(Person $person): \Illuminate\Http\JsonResponse
    {
        return $this->successResponse([
            'message' => 'ok',
            'client' => $person
        ]);
    }


    public function update(UpdatePersonRequest $request, Person $person): \Illuminate\Http\JsonResponse
    {
        $person->update($request->validated());

        return $this->successResponse([
            'message' => 'ok',
            'client' => $person
        ]);

    }


    public function destroy(Person $person): \Illuminate\Http\JsonResponse
    {

        if (!$person->delete()) {
            return $this->errorResponse([
                'message' => 'fail',
            ]);
        }

        return $this->successResponse([
            'message' => 'ok'
        ]);

    }

    public function restore($id)
    {
        $category = Person::withTrashed()->findOrFail($id);

        if (!$category->restore()) {
            return response()->json([
                'message' => 'fail',
            ]);
        }

        return response()->json([
            'message' => 'ok'
        ]);
    }
}
