<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $batchRef }}</title>
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

    {{-- INVOICE NUMBER --}}
    <div class="inv-row">INVOICE# {{ $batchRef }}</div>

    {{-- TO: --}}
    <div class="to-section">
        <strong>TO:</strong>
        <p>{{ $orgName }}</p>
    </div>

    <p style="font-size:11px;"><strong>Events:</strong> {{ $eventNames ?? '' }}</p>

    {{-- TABLE --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>NAME</th>
                <th>EVENT</th>
                <th>TYPE</th>
                <th>HOTEL</th>
                <th>SPOUSE</th>
                <th>EXTRAS</th>
                <th style="width:75px;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @foreach($bookings as $index => $b)
            @php $subtotal += $b->total_cost; @endphp
            <tr>
                <td class="c">{{ $index + 1 }}</td>
                <td>{{ $b->name }}</td>
                <td class="c">{{ $b->event->event_name ?? $b->event_id }}</td>
                <td class="c">{{ $b->member_type ?? 'Non-Member' }}</td>
                <td class="c">{{ $b->accommodation && $b->hotel ? $b->hotel->name : 'N/A' }}</td>
                <td class="c">{{ $b->spouse_included ? 'Yes' : 'No' }}</td>
                <td class="c">{{ $b->extras ?? 0 }}</td>
                <td class="r">{{ number_format($b->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @php
            $totalCredit = $bookings->sum('credit_applied');
            $totalDebt = $bookings->sum('debt_applied');
            $totalBalance = $bookings->sum('balance');
        @endphp
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right; font-weight:bold;">GRAND TOTAL</td>
                <td class="r" style="font-weight:bold;">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:right;">CREDIT</td>
                <td class="r" style="color:#d32f2f;">({{ number_format($totalCredit, 2) }})</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:right;">DEBT</td>
                <td class="r" style="color:#d32f2f;">{{ number_format($totalDebt, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:right; font-weight:bold; border-top:2px solid #006198;">TOTAL DUE</td>
                <td class="r" style="font-weight:bold; border-top:2px solid #006198;">{{ number_format($subtotal - $totalCredit + $totalDebt, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="font-size:10px; margin-top:4px;"><strong>Total Participants:</strong> {{ $totalPeople }} | <strong>With Accommodation:</strong> {{ $peopleWithAccommodation }}</p>

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
