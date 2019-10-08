<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->id }}</title>
</head>
<body>
<table>
    <tr>
        <td>
            <b>ID:</b>
        </td>
        <td>
            {{ $invoice->id }}
        </td>
    </tr>

    <tr>
        <td>
            <b>Client NIF:</b>
        </td>
        <td>
            {{ $invoice->nif}}
        </td>
    </tr>

    <tr>
        <td>
            <b>Client Name:</b>
        </td>
        <td>
            {{ $invoice->name}}
        </td>
    </tr>

    <tr>
        <td>
            <b>Responsable waiter:</b>
        </td>
        <td>
            {{ $invoice->waiterName}}
        </td>
    </tr>

    <tr>
        <td>
            <b>Date:</b>
        </td>
        <td>
            {{ $invoice->date}}
        </td>
    </tr>

    <tr>
        <td>
            <b>Total Price:</b>
        </td>
        <td>
            {{ $invoice->total_price }}
        </td>
    </tr>

    <tr>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>
            <b>Items:</b>
        </td>
        <td>

        </td>
    </tr>

    <tr>
        <td>

        </td>
        <td>
            <table>
                <thead>
                    <th><b>Name</b></th>
                    <th><b>Quantity</b></th>
                    <th><b>Unit Price</b></th>
                    <th><b>Sub total</b></th>
                </thead>
        </td>
    </tr>

    <tr>
        <td>

        </td>
        <td>
            <tbody>
                 @foreach($items as $item)
                    <tr>
                        <td>{{$item->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{$item->unit_price}}</td>
                        <td>{{$item->sub_total_price}}</td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </td>
    </tr>

</table>
</body>
</html>