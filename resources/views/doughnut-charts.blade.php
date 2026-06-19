@extends('layouts.app')

@section('content')

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doughnut Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="btn-group" role="group" id="chartButtons" style="margin-left: 100px;">
    @foreach ($chartData as $chart)
        <button class="btn btn-primary chartButton" data-chart-number="{{ $chart['question_number'] }}">Chart {{ $chart['question_number'] }}</button>
    @endforeach
</div>

@foreach ($chartData as $chart)
    <div class="chart-container" style="width: 300px; margin: 0 auto; display: none;">
        <h2 style="text-align: center">Question {{ $chart['question_number'] }}</h2>
        <div style="font-size: 18px">{{ $chart['question'] }}</div> <!-- Display the corresponding question here -->
        <canvas id="doughnutChart-{{ $chart['question_number'] }}"></canvas>
    </div>

    <script>
        // Retrieve data for the current chart
        const chartData{{ $chart['question_number'] }} = @json($chart['data']);

        // Extract labels and data for the chart
        const labels{{ $chart['question_number'] }} = chartData{{ $chart['question_number'] }}.map(item => item.answer);
        const data{{ $chart['question_number'] }} = chartData{{ $chart['question_number'] }}.map(item => item.count);
        const backgroundColor{{ $chart['question_number'] }} = chartData{{ $chart['question_number'] }}.map(() => '#' + Math.random().toString(16).substr(-6));

        // Create the doughnut chart with a unique canvas element
        const ctx{{ $chart['question_number'] }} = document.getElementById('doughnutChart-{{ $chart['question_number'] }}').getContext('2d');
        const chart{{ $chart['question_number'] }} = new Chart(ctx{{ $chart['question_number'] }}, {
            type: 'doughnut',
            data: {
                labels: labels{{ $chart['question_number'] }},
                datasets: [{
                    data: data{{ $chart['question_number'] }},
                    backgroundColor: backgroundColor{{ $chart['question_number'] }},
                    hoverBackgroundColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                    // Specify the hover background color for each data point
                }],
            },

            options: {
                cutout: 70, // Adjust this value to control the width of the doughnut
                circumference: 360, // Set to 70% of a full circle

            },
        });
    </script>
@endforeach

<script>
    const chartButtons = document.querySelectorAll('.chartButton');
    const chartContainers = document.querySelectorAll('.chart-container');

    chartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const chartNumber = button.getAttribute('data-chart-number');

            // Hide all chart containers
            chartContainers.forEach(container => {
                container.style.display = 'none';
            });

            // Show the selected chart container
            document.querySelector(`#doughnutChart-${chartNumber}`).parentNode.style.display = 'block';
        });
    });
    // Display the first chart by default
    document.querySelector('#doughnutChart-{{ $chartData[0]['question_number'] }}').parentNode.style.display = 'block';

</script>
</body>
</html>

@endsection
