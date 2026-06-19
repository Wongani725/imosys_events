<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $bookings[0]->booking_reference ?? $bookings[0]->bookingID }}</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; color: #333; font-size: 11px; }

        .center { text-align: center; }
        .logo { max-height: 75px; }

        .addr-table { width: 100%; margin-bottom: 4px; }
        .addr-table td { vertical-align: top; font-size: 11px; padding: 0; }
        .addr-left { width: 50%; }
        .addr-right { width: 50%; text-align: right; }
        .addr-table p { margin: 1px 0; }

        hr { border: none; border-top: 1.5px solid #000; margin: 6px 0; }

        .date-row { text-align: right; margin-bottom: 4px; font-size: 11px; }
        .inv-row { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 8px; }

        .to-section { margin-bottom: 8px; font-size: 11px; }
        .to-section p { margin: 1px 0; }

        table.items { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.items th { border: 1px solid #333; padding: 5px 4px; font-size: 10px; background: #006198; color: #fff; text-align: center; }
        table.items td { border: 1px solid #333; padding: 4px; font-size: 10px; }
        table.items .r { text-align: right; }
        table.items .c { text-align: center; }

        .event-header { background: #f0f4f8; font-weight: bold; font-size: 11px; }

        .bank-wrap { position: fixed; bottom: 25px; left: 30px; right: 30px; font-size: 11px; }
        .bank-wrap p { margin: 1px 0; }
    </style>
</head>
<body>

    {{-- LOGO --}}
    <div class="center"><img src="{{ public_path('images/alogo2.jpeg') }}" class="logo" alt="IIA Malawi Logo"></div>

    {{-- ADDRESS --}}
    <table class="addr-table">
        <tr>
            <td class="addr-left">
                <p>P. O. Box 31140</p>
                <p>Chichiri,</p>
                <p>Blantyre 3</p>
                <p>Malawi</p>
            </td>
            <td class="addr-right">
                <p>Tel: 0111 830 658</p>
                <p>E-mail: iiamalawi@iiamalawi.com</p>
            </td>
        </tr>
    </table>

    <hr>

    {{-- DATE --}}
    <div class="date-row"><strong>{{ now()->format('jS F Y') }}</strong></div>

    {{-- INVOICE --}}
    <div class="inv-row">INVOICE# {{ $bookings[0]->booking_reference ?? $bookings[0]->bookingID }}</div>

    {{-- TO --}}
    <div class="to-section">
        <strong>TO:</strong>
        <p>{{ $bookings[0]->name }}</p>
        @if($bookings[0]->company)<p>{{ $bookings[0]->company }}</p>@endif
        <p>{{ $bookings[0]->email }}</p>
    </div>

    {{-- TABLE --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:30px;">QTY</th>
                <th>DESCRIPTION</th>
                <th>EVENT</th>
                <th style="width:75px;">UNIT PRICE</th>
                <th style="width:75px;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $grandSubtotal = 0; @endphp
            @foreach($items as $section)
                @foreach($section['items'] as $item)
                @php $grandSubtotal += $item['total']; @endphp
                <tr>
                    <td class="c">{{ $item['qty'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['event_name'] ?: $section['event'] }}</td>
                    <td class="r">{{ number_format($item['price'], 2) }}</td>
                    <td class="r">{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="event-header">
                    <td colspan="4" style="text-align:right; font-weight:bold;">{{ $section['event'] }} Subtotal</td>
                    <td class="r" style="font-weight:bold;">{{ number_format($section['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">GRAND TOTAL</td>
                <td class="r" style="font-weight:bold;">{{ number_format($grandSubtotal, 2) }}</td>
            </tr>
            @php $totalCredits = collect($items)->sum('credit'); @endphp
            @php $totalDebts = collect($items)->sum('debt'); @endphp
            <tr>
                <td colspan="4" style="text-align:right;">CREDIT</td>
                <td class="r" style="color:#d32f2f;">({{ number_format($totalCredits, 2) }})</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right;">DEBT</td>
                <td class="r" style="color:#d32f2f;">{{ number_format($totalDebts, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold; border-top:2px solid #006198;">TOTAL DUE</td>
                <td class="r" style="font-weight:bold; border-top:2px solid #006198;">{{ number_format($grandSubtotal - $totalCredits + $totalDebts, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- BANK DETAILS --}}
    <div class="bank-wrap">
        <strong>Bank Details</strong>
        <p>Account Name: Institute of Internal Auditors</p>
        <p>Bank Name: First Capital Bank</p>
        <p>Account Number: 0700649006</p>
        <p>Account Type: Current</p>
        <p>Branch: Blantyre</p>
        <p>Swift Code: FRCGMWMW XXX</p>
    </div>

</body>
</html>