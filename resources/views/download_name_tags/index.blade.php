@extends('layouts.app')

@section('content')
        <!DOCTYPE html>
<html lang="en">
<head>
    <title>MLS Name Tags</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- External Libraries --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    {{-- Styles --}}
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }

        .name-tag, .program-image {
            width: 420px;
            height: 620px;
            border-radius: 10px;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 283px;
            align-items: center;
            padding-bottom: 40px;
            font-family: 'Poppins', sans-serif;
        }

        .program-image {
            margin-left: -8px;
        }


        .name-tag {
            background-image: url('{{ asset($image) }}');
            margin-right: 10px;
        }

        .qrcode-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .qrcode-wrapper img {
            width: 100px;
            height: 100px;
        }

        .participant-name {
            font-size: 22px;
            color: #000;
            font-weight: bold;
            text-align: center;
        }

        .company-name {
            font-size: 18px;
            color: #e7ae57;
            font-weight: bold;
            text-align: center;
        }

        .pdf-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 10px;
            padding: 10px;
            page-break-after: always;
            justify-items: center;
        }

        .meal-card {
            width: 420px;
            height: 620px;
            border: 1px solid #ddd;
            border-radius: 10px;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .meal-card img {
            width: 100%;
            height: auto;
        }

        @media print {
            .hide-on-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container mt-4 mb-3">

    {{-- Name Tag Filter Form --}}
    <form action="{{ route('download-name-tags') }}" method="POST" class="mb-4 hide-on-print">
        @csrf
        <input type="hidden" name="id" value="{{ $event_id }}">
        <label for="starting_id">Starting ID:
            <input type="number" name="starting_id" required>
        </label>
        <label for="ending_id">Ending ID:
            <input type="number" name="ending_id" required>
        </label>
        <button type="submit" class="btn" style="color: white; background-color: #e7ae57;">Display Name Tags</button>
    </form>

    {{-- Header --}}
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ $participants[0]->event_name }} Name Tags</h4>
            <button class="btn" style="color: white; background-color: #e7ae57;" id="downloadButton" onclick="downloadFormPDF()">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
    </div>

    {{-- Content for PDF --}}
    <div id="pdfContent">

        @foreach($participants as $index => $participant)
            @if($index % 4 === 0)
                <div class="pdf-page">
                    @endif

                    {{-- Name Tag --}}
                    <div class="name-tag">
                        <div class="qrcode-wrapper">
                            <img src="{{ route('qrcode', $participant->reference_code) }}" style="margin-top: 500px;" alt="QR Code">
                        </div>
                        <div class="participant-name text-capitalize">{{ $participant->participant }}</div>
                        <div class="company-name">{{ $participant->company_name }}</div>
                    </div>

                    @if($index % 4 === 3 || $loop->last)
                </div>

                {{--                 Program Page after every 4 name tags--}}
{{--                <div class="pdf-page">--}}
{{--                    @for ($i = 0; $i < 4; $i++)--}}
{{--                        <div class="program-image"--}}
{{--                             style="background-image: url('{{ asset('background_images/' . $event_id . '_programme.png') }}');">--}}
{{--                        </div>--}}
{{--                    @endfor--}}
{{--                </div>--}}




                {{-- Extra Meals (if any) --}}
                @if(!empty($participant->pageParticipantsExtraMeals))
                    @foreach($participant->pageParticipantsExtraMeals as $participantName => $extraMeals)
                        @foreach($extraMeals as $mealList)
                            @if(!empty($mealList))
                                <div class="pdf-page">
                                    @foreach($mealList as $mealIndex => $meal)
                                        <div class="meal-card">
                                            <h3>IIA MEAL CARD</h3>
                                            <h4>{{ $participantName }} {{ $mealIndex + 1 }}</h4>
                                            <img src="{{ route('qrcode', $meal->unique_code) }}" alt="Meal QR">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                @endif
            @endif
        @endforeach

    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function downloadFormPDF() {
        let clickedButton = $("#downloadButton");
        clickedButton.html(`<span class="spinner-border" role="status" aria-hidden="true"></span>`).prop("disabled", true);

        let pages = document.querySelectorAll(".pdf-page");
        let pdfContent = [];

        pages.forEach((page, index) => {
            setTimeout(() => {
                html2canvas(page, {
                    useCORS: true,
                    allowTaint: true,
                    scale: 2,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: page.offsetWidth,
                    windowHeight: page.offsetHeight
                }).then(canvas => {
                    let imgData = canvas.toDataURL("image/png");
                    pdfContent.push({
                        image: imgData,
                        width: 540,
                        height: 742,
                        marginBottom: 30
                    });

                    if (pdfContent.length === pages.length) {
                        const docDefinition = {
                            pageSize: { width: 595, height: 842 },
                            content: pdfContent
                        };
                        pdfMake.createPdf(docDefinition).download("name_tags.pdf");

                        clickedButton.html(`<i class="fas fa-download"></i> Download PDF`).prop("disabled", false);
                    }
                });
            }, index * 500);
        });
    }
</script>
</body>
</html>
@endsection
