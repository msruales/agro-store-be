<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\DetailBill;
use Carbon\Carbon;

class DashboardController extends ApiController
{
    function most_sold_products(): \Illuminate\Http\JsonResponse
    {

        $startWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);
        $last_seven_days = Carbon::now()->subDays(7);

        $products = DetailBill::select('product_id')->
        with(['product' => function ($query) {
            $query->select('id', 'name');
        }])
//            ->whereBetween('created_at', [$startWeek, $endWeek])->get()->groupBy('product_id')
            ->where('created_at', '>=', $last_seven_days)
            ->get()
            ->groupBy('product_id')
            ->map(function ($product) {
                return collect([
                    'total' => $product->count(),
                    'name' => $product[0]->product->name
                ]);
            })->sortByDesc(function ($test) {
                return $test['total'];
            })->take(5);

        return $this->successResponse($products);
    }
}
