<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends ApiController
{

    public function index()
    {
        $orders = Order::orderBy('id', 'desc')
            ->paginate(8);
        $pagination = $this->parsePaginationJson($orders);


        return $this->successResponse([
            'pagination' => $pagination,
            'orders' => $orders->items()
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        $date_now = Carbon::now();

        $data['date'] = $date_now;
        $data['user_id'] = $request->user()->id;

        $new_order = Order::create($data);

        $new_order->details()->createMany($data['details']);
        $new_order->load('user');

        return $this->successResponse($new_order);
    }


    public function show(Order $order)
    {
        return $this->successResponse($order);
    }

    public function update(Request $request, Order $order)
    {
        //
    }

    public function destroy(Order $order)
    {
        //
    }

    public function download(Order $order): \Illuminate\Http\Response
    {
        return Pdf::loadView('pdf.order-pdf', ['order' => $order])
            ->stream('pedido.pdf');
    }
}
