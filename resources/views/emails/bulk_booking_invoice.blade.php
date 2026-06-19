@component('mail::message')
# IIA Malawi — Bulk Booking Invoice

**Organization:** {{ $orgName }}
**Event:** {{ $eventName }}
**Batch Reference:** {{ $batchRef }}

Dear Sir/Madam,

Please find below the booking summary for **{{ $orgName }}**.

@component('mail::table')
| # | Name | Email | Amount (MWK) |
|---|---|---|---|
@foreach($bookings as $index => $b)
| {{ $index + 1 }} | {{ $b->name }} | {{ $b->email }} | {{ number_format($b->total_cost, 2) }} |
@endforeach
| **Total** | | | **{{ number_format($totalAmount, 2) }}** |
@endcomponent

**Next Steps:**
1. Review the participant list above.
2. Make payment to the account provided below.
3. Each participant can upload proof of payment.

**Bank Details:**
- **Account Name:** Institute of Internal Auditors Malawi
- **Bank:** First Capital Bank
- **Account Number:** 0700649006
- **Branch:** Blantyre
- **Account Type: Current
- **Swift Code: FRCGMWMW XXX


Please contact us for any queries regarding this invoice.

Regards,
**IIA Malawi Team**
@endcomponent
