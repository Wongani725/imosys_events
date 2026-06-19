@component('mail::message')
    {{$salutation}}

    {{$message}}

    Thanks,
    {{ config('app.name') }}
@endcomponent
