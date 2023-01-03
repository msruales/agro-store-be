<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Models\Bill;
use App\Models\DetailBill;
use App\Models\Person;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    function most_sold_products(): \Illuminate\Http\JsonResponse
    {

        $startWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);
        $last_seven_days = Carbon::now()->subDays(7);

        $products = DetailBill::select('product_id')->
        with(['product' => function ($query) {
            $query->select('id', 'name')->withTrashed();
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

    public function total_products(): \Illuminate\Http\JsonResponse
    {
        $products = Product::all()->count();

        return $this->successResponse($products);
    }

    public function total_clients(): \Illuminate\Http\JsonResponse
    {
        $clients = Person::all()->count();
        return $this->successResponse($clients);
    }


    public function utilityFor(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->query('type') ? $request->query('type') : 'month';

        $months = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


        $lastSales = Bill::withoutTrashed()->
        select('utility','total', 'date')
            ->when($type === 'month', function ($query) {
                $currentDate = Carbon::now()->month;
                $query->whereMonth('date', $currentDate);
            })
            ->when($type === 'year', function ($query) {
                $currentDate = Carbon::now()->year;
                $query->whereYear('date', $currentDate);
            })
            ->get()
            ->groupBy(function ($val) use ($type) {
                if ($type === 'month') {
                    return Carbon::parse($val->date)->format('d');
                }
                return Carbon::parse($val->date)->format('m');
            })
            ->map(function ($data, $index) use ($type, $months) {
                return [
                    'count' => $data->count(),
                    'total_sale' => $data->sum('total'),
                    'total_utility' => $data->sum('utility'),
                    'name' => $type === "year" ? $months[intval($index-1)] : $index,
                    'index' => intval($index)
                ];
            })->sortBy('index');

        return $this->successResponse($lastSales);
    }
}
