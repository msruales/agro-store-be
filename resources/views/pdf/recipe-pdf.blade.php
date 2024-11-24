<body style="margin: 0; padding: 0">
<style>
    @page {
        margin: 0cm 0cm;
    }

    body {
        font-size: 11px;
        font-family: Arial, 'Helvetica', sans-serif;
        margin: 0;
        padding: 0;
    }

    .accessKey {
        font-size: 8px;
    }

    .container {
        width: 265px;
        padding: 0 5px 8px 6px;
        margin-left: 10px;
    }

    h1 {
        font-size: 14px;
        text-align: center;
    }

    header, footer {
        text-align: center;
    }

    .info {
        margin-top: 4px;
    }

    .table-details {
        width: 100%;
        border-collapse: collapse;
    }

    .table-details th, .table-details td {
        border: 1px solid #000;
        padding: 2px;
    }

    .align-end {
        text-align: right;
    }

    .separator {
        border-bottom: 1px dashed #000;
    }
</style>

<div class="container">
    <header>
        <h1>.::{{mb_convert_case($settings['nombreComercial'], MB_CASE_TITLE, "UTF-8")}}::.</h1>
        <span>RUC {{$settings['ruc']}}</span><br>
        <span>{{mb_convert_case($settings['dirEstablecimiento'], MB_CASE_TITLE, "UTF-8")}}</span><br>
        {{--        <span>Telf. 2679 019</span>--}}
        @if($bill->type_voucher === 'invoice' && $bill->accessKey )
            <span>Clave de Acceso</span>
            <span class="accessKey">{{$bill->accessKey}}</span><br>
        @endif

    </header>

    <div class="info">
        <span>Cliente: {{mb_convert_case($bill->client->full_name, MB_CASE_TITLE, "UTF-8")}}</span><br>
        @if($bill->client->document_number)
            <span>{{$bill->client->document_type}}: {{$bill->client->document_number}}</span><br>
        @endif
        @if($bill->type_voucher === 'sales_note')
            <span>Nota de Venta: {{$bill->id}}</span><br>
        @endif
        @if($bill->type_voucher === 'invoice' && $bill->accessKey )
            <span>Fecha de Autorizacion: {{$bill->authorization_date}}</span>
            <br>
            <span>Factura: {{$settings['codEstablecimiento'].$settings['codPtoEmision'].str_pad($bill->sequential, 9, '0', STR_PAD_LEFT)}}</span>
            <br>
        @endif

        <span>Fecha y Hora: {{$bill->date}}</span>
    </div>

    <br>

    <table class="table-details">
        <thead>
        <tr>
            <th>CAN</th>
            <th>DESCRIPCIÓN</th>
            <th>PVP</th>
            <th>TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <tr class="separator">
            <td colspan="4"></td>
        </tr>
        @foreach($bill->details as $detail)
            <tr>
                <td class="align-end">{{$detail->quantity}}</td>
                <td>{{strtoupper($detail->product->name)}}</td>
                <td class="align-end">${{number_format($detail->price,2)}}</td>
                <td class="align-end">
                    ${{number_format((($detail->price - $detail->discount )*$detail->quantity),2)}}</td>
            </tr>
        @endforeach
        <tr class="separator">
            <td colspan="4"></td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <th class="align-end" colspan="2">SUBTOTAL:</th>
            <td class="align-end" colspan="2">${{number_format(($subtotal),2)}}</td>
        </tr>
        <tr>
            <th class="align-end" colspan="2">IMPUESTOS:</th>
            <td class="align-end" colspan="2">${{number_format(($bill->tax),2)}}</td>
        </tr>
        @if($total_discount > 0)
            <tr>
                <th class="align-end" colspan="2">DESCUENTOS:</th>
                <td class="align-end" colspan="2">${{number_format(($total_discount),2)}}</td>
            </tr>
        @endif
        <tr>
            <th class="align-end" colspan="2">TOTAL:</th>
            <td class="align-end" colspan="2">${{number_format(($bill->total),2)}}</td>
        </tr>
        </tfoot>
    </table>

    <br>
    <footer>
        <span>¡Gracias por su compra!</span>
    </footer>
</div>
</body>

