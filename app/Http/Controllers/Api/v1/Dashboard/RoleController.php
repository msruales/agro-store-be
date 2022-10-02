<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends ApiController
{
    function roles_for_select()
    {

        $roles = Role::orderBy('id', 'desc')->get();

        return $this->successResponse([
            'roles' => $roles
        ]);

    }
}
