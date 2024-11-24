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
</style>
<body>

<div class="container">
    <h1>Lista de Productos</h1>
    <table>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Categoria</th>
            <th>Costo</th>
            <th>Precio</th>
            <th>Stock</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)

            <tr>
                <td>{{$product->name}}</td>
                <td>{{$product->category->name}}</td>
                <td>$ {{number_format($product->cost, 2, '.', '')}}</td>
                <td>$ {{number_format($product->price, 2, '.', '')}}</td>
                <td>{{$product->stock}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>


