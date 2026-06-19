<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $breakdown['bookingID'] }}</title>

    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }

        .header { text-align: center; margin-bottom: 30px; }

        .header h1 { margin: 0; color: #006198; }

        .header p { margin: 3px 0; color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }

        th {
            background: #006198;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .total {
            font-weight: bold;
            font-size: 16px;
            color: #006198;
        }

        .meta {
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>IIA Malawi</h1>
    <p>{{ $breakdown['event_name'] }}</p>
    <p>{{ $breakdown['event_theme'] }}</p>
    <p><strong>INVOICE</strong></p>
</div>

<div class="meta">
    <p><strong>Invoice #:</strong> {{ $breakdown['bookingID'] }}</p>
    <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>

   <p><strong>Client:</strong>
        {{ $user->participant ?? $user->name }}
    </p>

    <p><strong>Email:</strong>
        {{ $user->email_address ?? $user->email }}
    </p>
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th width="80">Qty</th>
            <th width="150">Unit (MWK)</th>
            <th width="150">Total (MWK)</th>
        </tr>
    </thead>

    <tbody>
        @foreach($breakdown['items'] as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ number_format($item['unit'], 2) }}</td>
                <td>{{ number_format($item['total'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="3" class="total" style="text-align:right;">
                GRAND TOTAL
            </td>
            <td class="total">
                MWK {{ number_format($breakdown['total_cost'], 2) }}
            </td>
        </tr>
    </tfoot>
</table>

</body>
</html>