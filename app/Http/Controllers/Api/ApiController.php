<?php

namespace  App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Traits\PaginationJson;

class ApiController extends Controller {
    use ApiResponse;
    use PaginationJson;
}
