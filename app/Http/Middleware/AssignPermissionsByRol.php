<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignPermissionsByRol
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next, ...$roles): \Illuminate\Http\JsonResponse
    {

        if (!Auth::user()) {
            return $this->errorResponse('', 401, 'Unauthorized');
        }

        $current_rol = Auth::user()->role->name;

        if (in_array(strtolower($current_rol), $roles)) {
            return $next($request);
        }

        return $this->errorResponse('', 403, 'Forbidden');

    }
}
