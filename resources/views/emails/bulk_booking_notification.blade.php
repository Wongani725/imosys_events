@component('mail::message')
# {{ $title }}

Dear **{{ $recipientName }}**,

{{ $message }}

---

@if($batchRef)
This notification is regarding your bulk booking (**{{ $batchRef }}**).
@endif

Regards,<br>
{{ config('app.name') }}
@endcomponent
