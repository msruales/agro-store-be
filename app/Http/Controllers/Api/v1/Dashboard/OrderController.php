<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\Person;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        return $this->successResponse($order->load('details'));
    }

    public function update(Request $request, Order $order)
    {
        //
    }

    public function destroy(Order $order)
    {
        //
    }

    public function sendByEmail(Request $request, $id): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'client_id' => 'required',
        ]);

        $client_id = $request->get('client_id');

        try {

            $client = Person::where('id', $client_id)->first();

            $order = Order::where('id', $id)->first();

            $comercialName = Setting::where('key', 'nombreComercial')->first();

            $email = $client->email;

            $res = Mail::to($email)->send(new \App\Mail\SendRequest($order, $comercialName));

            Log::info('SEND EMAIL ORDER', ['order' => $res]);
            return $this->successResponse(true);
        }catch (\Exception $e){
            Log::info('CATCH SEND EMAIL ORDER', ['order' => $e]);
            Log::info('CATCH SEND EMAIL ORDER ENV MAIL_PASSWORD', ['order' => env('MAIL_PASSWORD', 'smtp.mailgun.org')]);

            return $this->successResponse(false);
        }

    }

    public function download(Order $order): \Illuminate\Http\Response
    {
        //TODO ARREGLAR PRODUCTOS ELIMINADOS

        $comercialName = Setting::where('key', 'nombreComercial')->first();

        return Pdf::loadView('pdf.order-pdf', ['order' => $order, 'comercialName' => $comercialName->value])
            ->stream('pedido.pdf');
    }
}
