<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreBillRequest;
use App\Http\Requests\Dashboard\UpdateBillRequest;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\Request;


class BillController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $type_search = $request->query('type_search') ? $request->query('type_search') : 'all';
        $date = $request->query('date') ? $request->query('date') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $bills = Bill::with('client')
            ->with('details')
//            ->when($status === 'active', function ($query) use ($search, $date) {
//                $query->when($date !== '', function ($query) use ($date) {
//                    return $query->whereDate('date', $date);
//                });
//            })
            ->when($status === 'all', function ($query) use ($search, $date) {
                $query->withTrashed();
            })
            ->when($status === 'deleted', function ($query) use ($search, $date) {
                $query->onlyTrashed();
            })
            ->when($date !== '', function ($query) use ($date) {
                return $query->whereDate('date', $date);
            })
            ->when($type_search !== 'all', function ($query) use ($type_search) {
                $query->where('type_pay', strtoupper($type_search));
            })
            ->whereRelation('client', 'full_name', 'LIKE', "%$search%")
            ->OrWhereRelation('client', 'document_number', 'LIKE', "%$search%")
            ->orderBy('id', 'desc')
            ->paginate($per_page);

        $pagination = $this->parsePaginationJson($bills);

        return $this->successResponse([
            'pagination' => $pagination,
            'bills' => $bills->items(),
        ]);
    }

    public function store(StoreBillRequest $request): \Illuminate\Http\JsonResponse
    {

        $data = $request->validated();

        $date_now = Carbon::now();

        $data['date'] = $date_now;
        $data['user_id'] = $request->user()->id;


        if ($data['type_pay'] === 'credit') {
            $data['status'] = 'UNPAID';
        } else {
            $data['status'] = 'PAID';
        }

        $data['type_pay'] =  strtoupper($data['type_pay']);

        $new_bill = Bill::create($data);

        $new_bill->details()->createMany($data['details']);

        $new_bill->load('details');

        $new_bill->load('client');
        $new_bill->load('user');

        return $this->successResponse([
            'bill' => $new_bill,
        ]);
    }

    public function show(Bill $bill): \Illuminate\Http\JsonResponse
    {
        return $this->successResponse([
            'bill' => $bill
        ]);
    }

    public function update(UpdateBillRequest $request, Bill $bill)
    {
//        $data = $request->validated();
//
//        $date_now = Carbon::now();
//
//        $data['date'] = $date_now;
//
//        $new_bill = $bill->update($data);
//        $new_bill->details()->updateMany($data['details']);
//        $new_bill->load('details');
//        return $this->successResponse([
//            'bill' =>$new_bill,
//        ]);
    }

    public function destroy(Bill $bill): \Illuminate\Http\JsonResponse
    {
        if (!$bill->delete()) {
            return $this->errorResponse();
        }
        return $this->successResponse();
    }

    public function restore($id): \Illuminate\Http\JsonResponse
    {
        $category = Bill::withTrashed()->findOrFail($id);

        if (!$category->restore()) {
            return $this->errorResponse();
        }

        return $this->successResponse();
    }

    public function lastSales(): \Illuminate\Http\JsonResponse
    {

        $currentYear = Carbon::now()->year;

        $lastSales = Bill::withoutTrashed()->
        select('total', 'date')
            ->whereYear('date', $currentYear)
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->date)->format('m');
            })
            ->map(function ($month) {
                return [
                    'sale' => $month->count(),
                    'total' => $month->sum('total')
                ];
            });

        return $this->successResponse($lastSales);
    }

}
