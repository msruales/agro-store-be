<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreBillRequest;
use App\Http\Requests\Dashboard\StorePersonRequest;
use App\Http\Requests\Dashboard\UpdateBillRequest;
use App\Http\Requests\Dashboard\UpdatePersonRequest;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ClientController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->query('status') ?? 'active';
        $search = $request->query('search') ?? '';
        $per_page = $request->query('per_page') ?? '10';

        $persons = Person::query();

        // Aplicar filtros según el estado
        if ($status === 'all') {
            $persons->withTrashed();
        } elseif ($status === 'deleted') {
            $persons->onlyTrashed();
        }

        // Aplicar búsqueda si se proporciona el parámetro 'search'
        if (!empty($search)) {
            $persons->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name)"), 'like', "%{$search}%");
            });
        }

        $persons = $persons->orderBy('id', 'desc')
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

        $clients = Person::select('first_name','last_name','id','document_number','document_type')->get();

        return $this->successResponse($clients);
    }

    public function selectWithEmail(Request $request): \Illuminate\Http\JsonResponse
    {

        $clients = Person::select('first_name','last_name','id','document_number','document_type','email')->where('email','!=', null)->get();

        return $this->successResponse($clients);
    }

    public function get_final_consumer() {
        $consumidorFinal = Person::where('document_number','9999999999999')->select('first_name','last_name','id','document_number','document_type')->first();
        return $this->successResponse($consumidorFinal);
    }

    public function store(StorePersonRequest $request): \Illuminate\Http\JsonResponse
    {

        $data_validated = $request->validated();
        $data_validated['first_name'] = strtoupper($data_validated['first_name']);
        $data_validated['last_name'] = strtoupper($data_validated['last_name']);

        $new_person = Person::create($data_validated);

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
        $data_validated = $request->validated();
        $data_validated['first_name'] = strtoupper($data_validated['first_name']);
        $data_validated['last_name'] = strtoupper($data_validated['last_name']);

        $person->update($data_validated);

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
