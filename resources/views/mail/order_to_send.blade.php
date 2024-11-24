<body>
<style>
    body {
        color: #000000;
        font: 14px/24px "Open Sans", "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", Sans-Serif;
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    th,
    td {
        padding: 6px 15px;
    }

    th {
        background: #1135ef;
        color: #fff;
        text-align: left;
    }

    tr:first-child th:first-child {
        border-top-left-radius: 6px;
    }

    tr:first-child th:last-child {
        border-top-right-radius: 6px;
    }

    td {
        border-right: 1px solid #c6c9cc;
        border-bottom: 1px solid #c6c9cc;
    }

    td:first-child {
        border-left: 1px solid #c6c9cc;
    }

    tr:nth-child(even) td {
        background: #eaeaed;
    }

    tr:last-child td:first-child {
        border-bottom-left-radius: 6px;
    }

    tr:last-child td:last-child {
        border-bottom-right-radius: 6px;
    }

    header {
        text-align: center;
        width: 100%;
    }

</style>

<div class="container">
    <header >
        <h1>{{$comercialName}}</h1><br>
    </header>
    <h3>Fecha: {{$order->date}}</h3>
    <table>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Cantidad</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->details as $detail)
            <tr>
                <td>{{$detail->product->name}}</td>
                <td style="text-align: center">{{$detail->quantity}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>


