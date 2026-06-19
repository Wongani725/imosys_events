@component('mail::message')
# IIA Malawi — Booking Invoice

Dear {{ $booking->name }},

Thank you for your booking with **IIA Malawi**.

Please find attached a PDF copy of your invoice for reference.

> **Booking Reference:** {{ $booking->booking_reference ?? $booking->bookingID }}
> **Status:** Pending Payment

**Next Steps:**
1. Review the attached invoice for your payment details.
2. Make payment to the account provided.
3. Upload your proof of payment via your member dashboard.

Your booking will be confirmed once payment has been verified.

@component('mail::button', ['url' => url('/member-dashboard')])
    View My Dashboard
@endcomponent

Thank you for your participation.

Regards,
**IIA Malawi Team**
@endcomponent
