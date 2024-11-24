<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Dashboard\StoreBillRequest;
use App\Models\Bill;
use App\Models\Setting;
use App\Models\Taxe;
use App\Models\User;
use App\Services\SRIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


class BillController extends ApiController
{

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $type_search = $request->query('type_search') ? $request->query('type_search') : 'ALL';
        $date = $request->query('date') ? $request->query('date') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $bills = Bill::with('client')
            ->with('details')
            ->when($status === 'all', function ($query) use ($search, $date) {
                $query->withTrashed();
            })
            ->when($status === 'deleted', function ($query) use ($search, $date) {
                $query->onlyTrashed();
            })
            ->when($date !== '', function ($query) use ($date) {
                return $query->whereDate('date', $date);
            })
            ->when($type_search !== 'ALL', function ($query) use ($type_search) {
                $query->where('type_pay', strtoupper($type_search));
            })
            ->where(function ($query) use ($search) {
                $query->whereRelation('client', function ($query) use ($search) {
                    $query->whereRaw("concat(first_name, ' ', last_name) like '%" . $search . "%' ");
                })
                    ->OrWhereRelation('client', 'document_number', 'LIKE', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($per_page);

        $pagination = $this->parsePaginationJson($bills);

        return $this->successResponse([
            'pagination' => $pagination,
            'bills' => $bills->items(),
        ]);
    }

    public function billsByClient(Request $request): \Illuminate\Http\JsonResponse
    {
        $client_id = $request->query('client_id') ? $request->query('client_id') : null;

        $status = $request->query('status') ? $request->query('status') : 'active';

        $search = $request->query('search') ? $request->query('search') : '';
        $type_search = $request->query('type_search') ? $request->query('type_search') : 'ALL';
        $date = $request->query('date') ? $request->query('date') : '';
        $per_page = $request->query('per_page') ? $request->query('per_page') : '10';

        $bills = Bill::with('client')
            ->with('details')
            ->when($status === 'all', function ($query) use ($search, $date) {
                $query->withTrashed();
            })
            ->when($status === 'deleted', function ($query) use ($search, $date) {
                $query->onlyTrashed();
            })
            ->when($date !== '', function ($query) use ($date) {
                return $query->whereDate('date', $date);
            })
            ->when($type_search !== 'ALL', function ($query) use ($type_search) {
                $query->where('type_pay', strtoupper($type_search));
            })
            ->where(function ($query) use ($search) {
                $query->whereRelation('client', function ($query) use ($search) {
                    $query->whereRaw("concat(first_name, ' ', last_name) like '%" . $search . "%' ");
                })
                    ->OrWhereRelation('client', 'document_number', 'LIKE', "%$search%");
            })
            ->where('client_id', $client_id)
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
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $date_now = Carbon::now();

            $data['date'] = $date_now;
            $data['user_id'] = $request->user()->id;


            if ($data['type_pay'] === 'credit') {
                $data['status'] = 'UNPAID';
            } else {
                $data['status'] = 'PAID';
            }

            $data['type_pay'] = strtoupper($data['type_pay']);

            $new_bill = Bill::create($data);


            $new_bill->details()->createMany($data['details']);

            $new_bill->load('details');
            $new_bill->load('client');
            $new_bill->load('user');

            if ($data['type_voucher'] === 'invoice') {

                $invoiceToSend = $this->generateJsonInvoice($new_bill);
                $resSRIService = SRIService::sendInvoice($invoiceToSend);

                if ($resSRIService['ok']) {
                    $sri_response = $resSRIService['data'];
                    $sequential = $this->getSequential();

                    $signedInvoiceXml = $sri_response['signedInvoiceXml'];
                    $accessKey = $sri_response['accessKey'];
                    $invoice_status = $sri_response['status'];
                    $messages = $sri_response['messages'];

                    // Guardando en base de datos
                    $new_bill->sequential = $sequential;
                    $new_bill->accessKey = $accessKey;
                    $new_bill->status_voucher = $invoice_status;
                    $new_bill->messages = $messages;
                    // Guardando xml
                    SRIService::storeXml($accessKey, $signedInvoiceXml, $invoice_status);

                    if($invoice_status === 'RECIBIDA'){
                        $resCheckStatus = SRIService::checkStatus($accessKey);
                        if ($resCheckStatus['ok']) {
                            //Todo fecha de autorizacion
                            $new_bill->status_voucher = $resCheckStatus['data']['status'];
                            SRIService::storeXml($accessKey, $resCheckStatus['data']['voucher'], $resCheckStatus['data']['status']);
                        }
                        $new_bill->messages = $resCheckStatus['messages'];
                    }

                } else {
                    $new_bill->status_voucher = 'SERVER-ERROR';
                }


                $new_bill->save();
            }


            DB::commit();
            return $this->successResponse([
                'bill' => $new_bill,
                'ok' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }


    }

    function sendInvoiceToSri($bill)
    {

    }

    public function tryCheckInvoice($id)
    {

        $new_bill = Bill::where('id',$id)->first();

        $invoiceToSend = $this->generateJsonInvoice($new_bill);
        $resSRIService = SRIService::sendInvoice($invoiceToSend);

        if ($resSRIService['ok']) {
            $sri_response = $resSRIService['data'];
            $sequential = $this->getSequential();

            $signedInvoiceXml = $sri_response['signedInvoiceXml'];
            $accessKey = $sri_response['accessKey'];
            $invoice_status = $sri_response['status'];
            $messages = $sri_response['messages'];

            // Guardando en base de datos
            $new_bill->sequential = $sequential;
            $new_bill->accessKey = $accessKey;
            $new_bill->status_voucher = $invoice_status;
            $new_bill->messages = $messages;
            // Guardando xml
            SRIService::storeXml($accessKey, $signedInvoiceXml, $invoice_status);

            if($invoice_status === 'RECIBIDA'){
                $resCheckStatus = SRIService::checkStatus($accessKey);
                if ($resCheckStatus['ok']) {
                    //Todo fecha de autorizacion
                    $new_bill->status_voucher = $resCheckStatus['data']['status'];
                    SRIService::storeXml($accessKey, $resCheckStatus['data']['voucher'], $resCheckStatus['data']['status']);
                }
                $new_bill->messages = $resCheckStatus['messages'];
            }

        } else {
            $new_bill->status_voucher = 'SERVER-ERROR';
        }

        $new_bill->update();

        return $this->successResponse($new_bill);
    }

    public function getSequential()
    {
        $lastValue = Bill::max('sequential') ?? 0;
        return $lastValue + 1;
    }

    public function validKeys(): array
    {
        return [
            'razonSocial',
            'nombreComercial',
            'ruc',
            'codEstablecimiento',
            'codPtoEmision',
            'dirMatriz',
            'dirEstablecimiento',
            'obligadoContabilidad',
            'passwordFirma',
            'nameFirma'
        ];
    }

    public function getSettings()
    {
        $settings = Setting::whereIn('key', $this->validKeys())->get()->toArray();

        return array_reduce($settings, function ($carry, $item) {
            return [
                ...$carry,
                $item['key'] => $item['value']
            ];
        }, []);
    }

    public function generateJsonInvoice($bill): array
    {

        $settings = $this->getSettings();

        $password = $settings['passwordFirma'];

        $details = $bill['details'];

        $iva = Taxe::where('is_active',true)->first();
        Log::info('IVA CURRENT IFO', ['iva' => $iva]);

        $ivaInfo = [
            'codigo' => $iva->codigo ?? 2,
            'codigoPorcentaje' => $iva->codigo_porcentaje ?? 4,
            'tarifa' => $iva->tarifa ?? 15
        ];

        $products = collect($details)->map(function ($item) use ($ivaInfo) {
            $codigoPrincipal = 'PR' . $item->product->id;

            $impuesto[] = $ivaInfo;

            return [
                "codigoPrincipal" => $codigoPrincipal,
                "descripcion" => $item->product->name,
                "cantidad" => $item->quantity,
                "precioUnitario" => (float)$item->price,
                "descuento" => (float)$item->discount,
                "impuestos" => $item->product->have_iva ? $impuesto : [],
            ];
        });

        $client = $bill->client;

        $tempUrl = $this->generateSignedUrl($settings['nameFirma']);

        $factura = [
            "infoTributaria" => [
                "razonSocial" => $settings['razonSocial'],
                "nombreComercial" => $settings['nombreComercial'],
                "ruc" => $settings['ruc'],
                "codEstablecimiento" => $settings['codEstablecimiento'],
                "codPtoEmision" => $settings['codPtoEmision'],
                "secuencial" => $this->getSequential(),
                "dirMatriz" => $settings['dirMatriz']
            ],

            "infoFactura" => [
                "fechaEmision" => Carbon::now()->format('d/m/Y'),
                "dirEstablecimiento" => $settings['dirEstablecimiento'],
                "obligadoContabilidad" => !($settings['obligadoContabilidad'] === '0'),
                "tipoIdentificacionComprador" => $this->getDocumentType($client),
                "razonSocialComprador" => $this->getFullName($client),
                "identificacionComprador" => $client->document_number,
                "direccionComprador" => $client->direction ?? 'Cuellaje',
                "correoComprador" => $client->email ? $client->email : null
            ],
            "pagos" => [
                [
                    "formaPago" => "01",
                    "total" => (float)$bill->total,
                    "plazo" => 0,
                    "unidadTiempo" => "dias"
                ]
            ],
            "detalles" => $products,
            "password" => Crypt::decryptString($password),
            "urlFirma" => $tempUrl,
        ];

        Log::info('infoFactura', ["info" => $factura]);
        return $factura;

    }

    public function generateSignedUrl($filename)
    {
        // Verifica si el archivo existe en la ruta privada
        if (!Storage::disk('local')->exists('private/sign/' . $filename)) {
            throw new Exception('Archivo no encontrado');
        }

        // Generar la URL temporal firmada (válida por 5 minutos)
        return URL::temporarySignedRoute(
            'download.signature',
            now()->addMinutes(5),
            ['filename' => $filename]
        );
    }

    function getDocumentType($client): string
    {

        //  RUC 04
        //  CÉDULA 05
        //  PASAPORTE 06
        //  VENTA A CONSUMIDOR FINAL* 07
        //  IDENTIFICACIÓN DEL EXTERIOR* 08

        if ($client->document_type === 9999999999999 || $client->document_type === '9999999999999') {
            return '07';
        }
        $tiposIdentificacion = [
            'CI' => '05',
            'RUC' => '04',
            'PASSPORT' => '06'
        ];
        return $tiposIdentificacion[$client->document_type] ?? '05';
    }

    function getFullName($client): string
    {
        return "$client->first_name $client->last_name";
    }

    public function show(Bill $bill): \Illuminate\Http\JsonResponse
    {
        return $this->successResponse([
            'bill' => $bill
        ]);
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

    public function downloadSignature($filename)
    {
        $filePath = storage_path('app/private/sign/' . $filename);

        // Verifica si el archivo existe
        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Devuelve el archivo como descarga
        return response()->download($filePath);
    }

    public function download($id): \Illuminate\Http\Response
    {

        $bill = Bill::where('id', $id)->first();

        $settings = $this->getSettings();

        $subtotal = $bill->details->reduce(function ($carry, $item) {
            return $carry + ($item->quantity * ($item->price-$item->discount));
        }, 0);
        $totalDiscount = $bill->details->reduce(function ($carry, $item) {
            return $carry + $item->discount;
        }, 0);

        $pdf = Pdf::loadView('pdf.recipe-pdf', ['bill' => $bill, 'settings' => $settings, 'subtotal' => $subtotal, 'total_discount' => $totalDiscount]);

        return $pdf->download('recipe.pdf');
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

    public function totalPerDayByUser(): \Illuminate\Http\JsonResponse
    {

        $date = Carbon::today();

        $users = User::all();

        $salesByUser = [];

        foreach ($users as $user) {

            $info = Bill::with('user')->where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->selectRaw('type_pay, SUM(total) as total_sales')
                ->groupBy('type_pay')
                ->get();

            $parsedInfo = $info->map(function ($data) use ($user) {
                return [
                    'user_id' => $user->id,
                    'total_sales' => $data['total_sales'],
                    'type_pay' => $data['type_pay'],
                ];
            });

            $salesByUser[] = $parsedInfo;
        }

        return $this->successResponse($salesByUser);
    }

}
