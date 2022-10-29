<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreUserRequest;
use App\Http\Requests\Dashboard\UpdateUserRequest;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    function index(Request $request)
    {
        $status = $request->query('status') ? $request->query('status') : '';
        $search = $request->query('search') ? $request->query('search') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $users = User::
        when($status === 'all', function ($query) use ($search) {
            $query->withTrashed();
        })
            ->when($status === 'deleted', function ($query) use ($search) {
                $query->onlyTrashed();
            })
            ->whereRelation('person', function($query) use($search){
                $query->whereRaw("concat(first_name, ' ', last_name) like '%" . $search . "%' ");
            })
            ->orderBy('id', 'desc')
            ->paginate($per_page);

        $pagination = $this->parsePaginationJson($users);

        return $this->successResponse([
            'pagination' => $pagination,
            'users' => $users->items()
        ]);

    }

    function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();

            $person = new Person();

            $person->first_name = $data['first_name'];
            $person->last_name = $data['last_name'];
            $person->document_type = $data['document_type'];
            $person->document_number = $data['document_number'];
            $person->direction = $data['direction'];
            $person->phone_number = $data['phone_number'];
            $person->email = $data['email'];

            $person->save();

            try {
                $user = new User();

                $user->role_id = $data['role_id'];
                $user->password = Hash::make($data['password']);

                $person->user()->save($user);

                $user->createToken('MyTokenAppVivero')->accessToken;
                $user->role;

                return $this->successResponse([
                    'message' => 'ok',
                    'user' => $user
                ]);
            } catch (\Exception $e) {
                $person->forceDelete();
                return $this->errorResponse($e->getMessage());
            }

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

    }

    function update(UpdateUserRequest $request, User $user)
    {
        $data_validated = $request->validated();

        $user->role_id = $data_validated['role_id'];

        if (isset($data_validated['password'])) {
            $user->password = Hash::make($data_validated['password']);
        }

        $user->person->first_name = $data_validated['first_name'];
        $user->person->last_name = $data_validated['last_name'];
        $user->person->document_type = $data_validated['document_type'];
        $user->person->document_number = $data_validated['document_number'];
        $user->person->email = $data_validated['email'];
        $user->person->direction = $data_validated['direction'];
        $user->person->phone_number = $data_validated['phone_number'];

        if ($user->update() && $user->person->update()) {
            return $this->successResponse([
                'user' => $user
            ]);
        }

        return $this->errorResponse([]);

    }

    function destroy(User $user)
    {
        if (!$user->delete()) {
            return $this->errorResponse();
        }
        return $this->successResponse();
    }

    function restore($id)
    {
        $category = User::withTrashed()->findOrFail($id);

        if (!$category->restore()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }
}
