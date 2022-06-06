<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationJson
{

    public function parsePaginationJson(LengthAwarePaginator $data)
    {
        return [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
        ];
    }
}
