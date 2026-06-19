<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; font-family: 'Segoe UI', sans-serif; }
        .resource-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.2s; }
        .resource-card:hover { transform: translateY(-2px); }
        .event-section { border-left: 4px solid #006198; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="text-primary">Event Resources</h2>
            <p class="text-muted">Welcome, {{ $firstParticipant->participant }}</p>
        </div>

        @foreach($events as $event)
            @php
                $eventDocs = $documents->where('event_id', $event->event_id);
                $confirmed = $participants->firstWhere('event_id', $event->event_id);
            @endphp
            @if($confirmed)
            <div class="card resource-card event-section mb-4 p-4">
                <div class="card-body">
                    <h4 class="text-primary">{{ $event->event_name }}</h4>
                    <p class="text-muted small">
                        {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} —
                        {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                        @if($event->event_venue) | {{ $event->event_venue }}@endif
                    </p>

                    @if($event->program_pdf)
                        <a href="{{ asset($event->program_pdf) }}" target="_blank" class="btn btn-primary me-2 mb-2">
                            <i class="fas fa-file-pdf"></i> View Programme
                        </a>
                    @endif

                   

                    @if($confirmed->reference_code)
                        <a href="{{ route('show-participant', ['reference_code' => $confirmed->reference_code]) }}"
                           class="btn btn-outline-info me-2 mb-2">
                            <i class="fas fa-tag"></i> View Name Tag
                        </a>
                    @endif

                    @if($eventDocs->count())
                        <hr>
                        <h6>Documents</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($eventDocs as $doc)
                                <a href="{{ asset($doc->file_path) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file"></i> {{ $doc->title }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif
        @endforeach

        <div class="text-center mt-4">
            <a href="{{ route('participant.login') }}" class="text-muted small">Participant Login</a>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
