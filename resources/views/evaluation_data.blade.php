@extends('layouts.app')



@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container">
        <h2 style="text-align:center;">Evaluation Data</h2>

        <button id="exportExcelButton"  class="btn btn-primary">Export to Excel</button>
        <button id="exportPdfButton"  class="btn btn-primary">Export to PDF</button>
        <table id="evaluationTable"  class="table table-bordered">

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Name</th>
                    @php
                        $sequentialNumbers = [
                          82 => 1,
                             83 => 2,
                            84 => 3,
                            85 => 4,
                            86 => 5,
                            87 => 6,
                            88 => 7,
                            89 => 8,
                            90 => 9,
                            91 => 10,
                            92 => 11,
                        ];
                    @endphp
                    @foreach ($sequentialNumbers as $questionId => $number)
                        <th>{{ $number }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @php
                    $previousName = null;
                @endphp
                @foreach ($evaluationData as $data)
                    @if ($data->name != $previousName)
                        <tr>
                            <td>{{ $data->name }}</td>
                            @php
                                $previousName = $data->name;
                            @endphp
                            @foreach ($sequentialNumbers as $questionId => $number)
                                @php
                                    $answer = '';
                                @endphp
                                @foreach ($evaluationData as $row)
                                    @if ($row->name == $data->name && $row->question_id == $questionId)
                                        @php
                                            $answer = $row->answer;
                                        @endphp
                                        @break
                                    @endif
                                @endforeach
                                <td>{{ $answer }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>


    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/vfs_fonts.js"></script>

    <script>
        function exportTableToExcel() {
            const table = document.getElementById('evaluationTable');
            const rows = table.querySelectorAll('tr');
            let csv = [];

            for (const row of rows) {
                const cols = row.querySelectorAll('td, th');
                const rowArray = [];
                for (const col of cols) {
                    rowArray.push(col.textContent.trim());
                }
                csv.push(rowArray.join(','));
            }

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const blobUrl = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = 'evaluation_data.csv';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function exportTableToPdf() {
            const table = document.getElementById('evaluationTable');
            const exportButton = document.getElementById('exportPdfButton');

            // Capture the table as an image using html2canvas
            html2canvas(table, {
                useCORS: true,
                allowTaint: true,
                scale: 2,
                scrollX: 0,
                scrollY: 0,
                windowWidth: table.offsetWidth,
                windowHeight: table.offsetHeight,
                backgroundColor: null // Set background color to null for transparent background
            }).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new pdfMake.createPdf({
                    content: [
                        {
                            image: imgData,
                            width: 500 // Adjust the width as needed
                        }
                    ]
                });

                // Download the PDF
                pdf.download('evaluation_data.pdf');
            });
        }

        const exportExcelButton = document.getElementById('exportExcelButton');
        const exportPdfButton = document.getElementById('exportPdfButton');
        exportExcelButton.addEventListener('click', exportTableToExcel);
        exportPdfButton.addEventListener('click', exportTableToPdf);
    </script>
@endsection
