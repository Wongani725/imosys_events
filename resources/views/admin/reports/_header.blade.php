<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">{{ $title }}</h2>
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" action="{{ route('admin.reports.show', ['type' => request()->route('type')]) }}" class="d-flex gap-2 align-items-center">
            <select name="event_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto;">
                @foreach($events as $ev)
                    <option value="{{ $ev->event_id }}" {{ $selectedEvent?->event_id === $ev->event_id ? 'selected' : '' }}>{{ $ev->event_name }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> All Reports</a>
        </form>
    </div>
</div>
