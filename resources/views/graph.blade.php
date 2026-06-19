@extends('layouts.app')

@section('content')
    <div class="card">
        <h2 style="text-align: center">Statistical Evaluation Graph - Regular Questions</h2>
        <canvas id="regularQuestionsChart" width="400" height="200"></canvas>
    </div>

    <br>

    <div class="card">
        <h2 style="text-align: center">Statistical Evaluation Graph - Speakers' Ratings</h2>
        <canvas id="speakersRatingsChart" width="400" height="200"></canvas>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script>
        // Get the regular questions chart data passed from the controller
        var regularQuestionsChartData = @json($regularQuestionsChartData);

        // Get the speakers' ratings chart data passed from the controller
        var speakersRatingsChartData = @json($speakersRatingsChartData);

        // Get the canvas element for regular questions
        var regularQuestionsCtx = document.getElementById('regularQuestionsChart').getContext('2d');

        // Get the canvas element for speakers' ratings
        var speakersRatingsCtx = document.getElementById('speakersRatingsChart').getContext('2d');

        // Create an array of question IDs for labels for regular questions
        var regularQuestionIds = Object.keys(regularQuestionsChartData);

        // Create an array of calculated scores for data for regular questions
        var regularQuestionScores = regularQuestionIds.map(function(questionId) {
            return regularQuestionsChartData[questionId];
        });

        // Create an array of speaker names from the controller
        var speakerNames = @json($speakerNames);

        var regularQuestionIds = [...Array(Object.keys(regularQuestionsChartData).length).keys()].map(x => x + 1);

        // Create the regular questions chart
        new Chart(regularQuestionsCtx, {
            type: 'line',
            data: {
                labels: regularQuestionIds,
                datasets: [{
                    label: 'Statistical Scores (Regular Questions)',
                    data: regularQuestionScores,
                    backgroundColor: regularQuestionScores.map(score => score > 50 ? 'rgba(0, 255, 0, 0.3)' : 'rgba(255, 0, 0, 0.3)'),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: true,
                    cubicInterpolationMode: 'monotone',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(...regularQuestionScores) + 101,
                        title: {
                            display: true,
                            text: 'Participant Sentiment (Regular Questions)',
                            font: {
                                size: 16
                            }
                        },
                    }
                }
            }
        });

        // Create the speakers' ratings chart with separate lines for each radio label and different colors
        new Chart(speakersRatingsCtx, {
            type: 'line',
            data: {
                labels: speakerNames,
                datasets: @json($labels).map(function(label, index) {
                    return {
                        label: label,
                        data: speakerNames.map(function(speakerName) {
                            return speakersRatingsChartData[label][speakerName] || 0;
                        }),
                        borderColor: getRandomColor(), // Generate a random color for each line
                        borderWidth: 2, // You can adjust the line width as needed
                        fill: false, // Prevent filling the area under the line
                        cubicInterpolationMode: 'monotone',
                    };
                })
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(...speakerNames.map(function(speakerName) {
                            return Math.max(...@json($labels).map(function(label) {
                                return speakersRatingsChartData[label][speakerName] || 0;
                            }));
                        })) + 10,
                        title: {
                            display: true,
                            text: 'Participant Sentiment (Speakers\' Ratings)',
                            font: {
                                size: 16
                            }
                        },
                    }
                }
            }
        });

        // Function to generate a random color
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    </script>
@endsection
