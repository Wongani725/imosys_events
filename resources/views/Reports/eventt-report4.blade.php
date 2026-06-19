<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-labels@1.1.0/dist/chartjs-plugin-labels.min.js"></script>


<style>
    .pdf-page {
        /*display: grid;*/
        /*grid-template-columns: repeat(2, 1fr);*/
        gap: 20px;
        margin-bottom: 40px;
    }

    .pdf-only {
        display: none;
    }

</style>


@extends('layouts.app')

@section('content')

    <div class="row mb-3">
        <div class="col-sm-12 col-md-3 mb-2">
            <div class="dropdown">
                <button class="btn dropdown-toggle" style="background-color: #696cff; color: white;" type="button" id="eventDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Choose an event
                </button>
                <ul class="dropdown-menu" aria-labelledby="eventDropdown">
                    @foreach(\App\Models\Event::pluck('event_name') as $eventName)
                        <li><a class="dropdown-item" href="{{ route('event-report4', ['event' => $eventName]) }}">{{ $eventName }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 text-md-center mb-2 text-sm-start">
            <h1 class="text-center" style="font-size: 24px; font-weight: bold;">{{ $event->event_name }}</h1>
        </div>
        <div class="col-12 col-md-3 text-md-end text-sm-start mb-3">
            <button type="button" onclick="downloadFormPDF()" class="btn btn-block" style="background-color: #696cff; color: white;">Download Dashboard</button>
        </div>

    </div>
    {{--download dashboard start point--}}

    <div id="pdfContent" style="display: none;"></div>
    <?php
    $index = 1; // Declare and initialize the variable
    $index++; // Increment the value of $index by 1
    ?>
    @if($index)
        <div class="pdf-page">

            <h1 class="text-center pdf-only" style="font-size: 24px; font-weight: bold;">{{ $event->event_name }} Dashboard</h1>


            <div class="row mb-3">
                <div class="col-sm-6 col-md-4 mb-3 mr-md-3">
                    <div class="card">
                        <div class=" rounded p-3" style="background-color: #696cff; color: white; max-height: 300px;">
                            <h2 class="text-center text-white mb-0" style="font-size: medium">Participants expected</h2>
                            <h2 class="text-center text-white fw-bold" id="totalInitialRegistrations" style="font-size: xx-large"></h2>
                        </div>
                        <canvas id="initialRegistrationsChart" style="max-height: 270px; width: 100%; margin: 0 auto;"></canvas>
                        <div class="d-flex justify-content-center">
                            <li class="ct-series-0 d-flex flex-column mr-4">
                                <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>

                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #aa2c13; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalMembers"></div>
                            </li>
                            <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->
                            <li class="ct-series-1 d-flex flex-column">
                                <h5 class="mb-0" style="font-size: 12px">Non Members</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #fbab62; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalNonMembers"></div>
                            </li>
                        </div>

                    </div>
                </div>

                <div class="col-sm-6 col-md-4 mb-3">
                    <div class="card">
                        <div class="rounded p-3" style="background-color: #696cff; color: white;max-height: 340px;">
                            <h2 class="text-center text-white mb-0" style="font-size: medium">Walk-in participants</h2>
                            <h2 class="text-center text-white fw-bold" id="totalRedeemedConferencePack" style="font-size: xx-large"></h2>
                        </div>
                        <canvas id="walkInParticipantsChart" class="centralize-graph" style="max-height: 270px; width: 100%; margin: 0 auto;"></canvas>

                        <div class="d-flex justify-content-center">
                            <li class="ct-series-0 d-flex flex-column mr-4">

                                <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #01949a; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalMembersAttendedd"></div>
                            </li>
                            <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->
                            <li class="ct-series-1 d-flex flex-column">
                                <h5 class="mb-0" style="font-size: 12px">Non Members</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#01dee7; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalNonMembersAttendedd"></div>
                            </li>
                        </div>
                    </div>

                </div>

                <div class="col-sm-6 col-md-4 mb-3">
                    <div class="card">
                        <div class="rounded p-3" style="background-color: #696cff; color: white;max-height: 340px;">
                            <h2 class="text-center text-white mb-0" style="font-size: medium">Total attended</h2>
                            <h2 class="text-center text-white fw-bold" id="totalAttended" style="font-size: xx-large"></h2>
                        </div>
                        <canvas id="TotalAttendedChart" class="centralize-graph" style="max-height: 270px; width: 100%; margin: 0 auto;"></canvas>
                        <div class="d-flex justify-content-center">
                            <li class="ct-series-0 d-flex flex-column mr-4">
                                <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>

                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#37a739 ; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalMembersAttended"></div>
                            </li>
                            <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->
                            <li class="ct-series-1 d-flex flex-column">
                                <h5 class="mb-0" style="font-size: 12px">Non Members</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#7fd581; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalAttendedNonMembers"></div>
                            </li>
                        </div>
                    </div>
                </div>
            </div>




            <div class="row mb-3">
                <div class="col-sm-6 col-md-4 mb-3" >
                    <div class="card">
                        <div class="rounded p-3" style="background-color: #696cff; color: white;max-height: 340px;">
                            <h2 class="text-center text-white mb-0"style="font-size: medium">Total meals</h2>
                            <h2 class="text-center text-white fw-bold" id="totalMeals" style="font-size: xx-large"></h2>
                        </div>
                        <canvas id="totalMealsChart" class="centralize-graph"  style="max-height: 270px; width: 100%; margin: 0 auto;"></canvas>

                        <div class="d-flex justify-content-center">
                            <li class="ct-series-0 d-flex flex-column mr-4">
                                <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Premium</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#fee802 ; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalMembersMeals"></div>
                            </li>

                            <li class="ct-series-1 d-flex flex-column">
                                <h5 class="mb-0" style="font-size: 12px">Extras</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #fcf9c5; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalNonMembersMeals"></div>

                            </li>
                        </div>
                    </div>
                </div>

                <div class="col-sm-16 col-md-8 mb-3">

                    <div class="card">
                        <div class="rounded p-3" style="background-color: #696cff; color: white;max-height: 340px;">
                            <h2 class="text-center text-white mb-0"style="font-size: medium">Total meals redeemed</h2>
                            <h2 class="text-center text-white fw-bold" id="totalMealsRedeemed"style="font-size: xx-large"></h2>
                        </div>
                        <canvas id="totalMealsRedeemedChart" class="centralize-graph"  style="max-height: 270px; width: 100%; margin: 0 auto;"></canvas>

                        <div class="d-flex justify-content-center">
                            <li class="ct-series-0 d-flex flex-column mr-4">
                                <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Premium scans</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:rgba(54, 162, 235, 0.8) ; width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalPremiumScans"></div>
                            </li>
                            <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->
                            <li class="ct-series-1 d-flex flex-column">
                                <h5 class="mb-0" style="font-size: 12px">Extras scans</h5>
                                <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:rgba(255, 99, 132, 0.8); width: 30px; height: 6px;"></span>
                                <div class="text-muted" id="totalExtrasScans"></div>
                            </li>
                        </div>
                    </div>
                </div>


            </div>


            <script>
                // Initial Registrations Chart
                var initialRegistrationsCtx = document.getElementById('initialRegistrationsChart').getContext('2d');
                var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Members', 'Non-Members'],
                        datasets: [{
                            label: 'Total Initial Registrations',
                            data: [@foreach($initialRegistrations as $registration){{ $registration->total_members }}, {{ $registration->total_non_members }}, @endforeach],
                            backgroundColor: [
                                // 'rgba(54, 162, 235, 0.8)',
                                '#aa2c13',
                                '#fbab62',
                                // 'rgba(255, 99, 132, 0.8)',
                                // 'rgba(255, 205, 86, 0.9)',
                            ],
                            borderColor: '#fff',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        cutout: 0,
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12, // Decreased legend font size
                                    },
                                    generateLabels: function (chart) {
                                        var data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            var total = data.datasets[0].data.reduce(function (a, b) {
                                                return a + b;
                                            }, 0);

                                            return data.labels.map(function (label, i) {
                                                var ds = data.datasets[0];
                                                var arc = chart.getDatasetMeta(0).data[i];
                                                var value = ds.data[i];
                                                var percentage = ((value / total) * 100).toFixed(2); // Calculate percentage

                                                var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';

                                                return {
                                                    text: label + ': ' + percentage + '%', // Include percentage in label text
                                                    fillStyle: backgroundColor,
                                                    strokeStyle: '#fff',
                                                    lineWidth: 1,
                                                    hidden: isNaN(ds.data[i]),
                                                    index: i,
                                                    // var data = chart.data;
                                                    // if (data.labels.length && data.datasets.length) {
                                                    //     return data.labels.map(function (label, i) {
                                                    //         var ds = data.datasets[0];
                                                    //         var arc = chart.getDatasetMeta(0).data[i];
                                                    //         var value = ds.data[i];
                                                    //         var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';
                                                    //
                                                    //         return {
                                                    //             text: label + "\n" + value,
                                                    //             fillStyle: backgroundColor,
                                                    //             strokeStyle: '#fff',
                                                    //             lineWidth: 1,
                                                    //             hidden: isNaN(ds.data[i]),
                                                    //             index: i,
                                                };
                                            });
                                        }
                                        return [];
                                    },
                                },
                            },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (context) {
                                    var label = context.label || '';
                                    var dataset = context.dataset;
                                    var index = context.dataIndex;
                                    var value = dataset.data[index];

                                    if (label) {
                                        label = dataset.label + ' - ' + label + ': ' + value;
                                    }
                                    return label;
                                }
                            },
                            titleFontSize: 16, // Decreased tooltip title font size
                            bodyFontSize: 14, // Decreased tooltip body font size
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20,
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 1,
                            }
                        }
                    }
                });


                {{--var initialRegistrationsCtx = document.getElementById('initialRegistrationsChart').getContext('2d');--}}
                {{--var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
                {{--    type: 'pie',--}}
                {{--    data: {--}}
                {{--        labels: ['Members', 'Non-Members'],--}}
                {{--        datasets: [{--}}
                {{--            label: 'Total Initial Registrations',--}}
                {{--            data: [@foreach($initialRegistrations as $registration){{ $registration->total_members }}, {{ $registration->total_non_members }}, @endforeach],--}}
                {{--            backgroundColor: [--}}
                {{--                '#aa2c13',--}}
                {{--                '#fbab62',--}}
                {{--            ],--}}
                {{--            borderColor: '#fff',--}}
                {{--            borderWidth: 1,--}}
                {{--        }],--}}
                {{--    },--}}
                {{--    options: {--}}
                {{--        cutout: 0,--}}
                {{--        responsive: true,--}}
                {{--        plugins: {--}}
                {{--            legend: {--}}
                {{--                display: false,--}}
                {{--            },--}}
                {{--            tooltip: {--}}
                {{--                callbacks: {--}}
                {{--                    label: function(context) {--}}
                {{--                        var label = context.label || '';--}}
                {{--                        var dataset = context.dataset;--}}
                {{--                        var index = context.dataIndex;--}}
                {{--                        var value = dataset.data[index];--}}
                {{--                        var total = dataset.data.reduce(function(a, b) {--}}
                {{--                            return a + b;--}}
                {{--                        }, 0);--}}
                {{--                        var percentage = ((value / total) * 100).toFixed(2);--}}

                {{--                        if (label) {--}}
                {{--                            label = dataset.label + ': ' + label + ': ' + value + ' (' + percentage + '%)';--}}
                {{--                        }--}}
                {{--                        return label;--}}
                {{--                    }--}}
                {{--                },--}}
                {{--                titleFontSize: 16,--}}
                {{--                bodyFontSize: 14,--}}
                {{--            },--}}
                {{--        },--}}
                {{--        layout: {--}}
                {{--            padding: {--}}
                {{--                left: 20,--}}
                {{--                right: 20,--}}
                {{--                top: 20,--}}
                {{--                bottom: 20,--}}
                {{--            }--}}
                {{--        },--}}
                {{--        elements: {--}}
                {{--            arc: {--}}
                {{--                borderWidth: 1,--}}
                {{--                render: function(args) {--}}
                {{--                    var ctx = args._chart.ctx;--}}
                {{--                    var dataset = args._chart.config.data.datasets[0];--}}
                {{--                    var total = dataset.data.reduce(function(a, b) {--}}
                {{--                        return a + b;--}}
                {{--                    }, 0);--}}
                {{--                    var value = dataset.data[args.dataIndex];--}}
                {{--                    var percentage = ((value / total) * 100).toFixed(2);--}}

                {{--                    ctx.textBaseline = 'middle';--}}
                {{--                    ctx.font = '12px Arial';--}}
                {{--                    ctx.fillStyle = '#fff';--}}

                {{--                    var centerX = args.x;--}}
                {{--                    var centerY = args.y;--}}
                {{--                    var radius = args.radius;--}}
                {{--                    var startAngle = args.startAngle;--}}
                {{--                    var endAngle = args.endAngle;--}}
                {{--                    var midAngle = startAngle + (endAngle - startAngle) / 2;--}}

                {{--                    var x = centerX + Math.cos(midAngle) * radius * 0.7;--}}
                {{--                    var y = centerY + Math.sin(midAngle) * radius * 0.7;--}}

                {{--                    ctx.fillText(percentage + '%', x, y);--}}
                {{--                }--}}
                {{--            }--}}
                {{--        }--}}
                {{--    }--}}
                {{--});--}}


                // Initial Registrations Chart
                var initialRegistrationsCtx = document.getElementById('walkInParticipantsChart').getContext('2d');
                var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Members', 'Non-Members'],
                        datasets: [{
                            label: 'Total Walkins registration',
                            data: [@foreach($walkinParticipants as $registration){{ $registration->total_walkin_members }},{{ $registration->total_walkin_non_members }}  @endforeach],
                            backgroundColor: [
                                //'rgba(54, 162, 235, 0.8)',
                                //'rgba(255, 99, 132, 0.8)',
                                //'rgba(255, 205, 86, 0.9)',
                                '#01949a',
                                '#01dee7'
                            ],
                            borderColor: '#fff',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12, // Decreased legend font size
                                    },
                                    generateLabels: function (chart) {
                                        var data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map(function (label, i) {
                                                var ds = data.datasets[0];
                                                var arc = chart.getDatasetMeta(0).data[i];
                                                var value = ds.data[i];
                                                var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';

                                                return {
                                                    text: label + "\n" + value,
                                                    fillStyle: backgroundColor,
                                                    strokeStyle: '#fff',
                                                    lineWidth: 1,
                                                    hidden: isNaN(ds.data[i]),
                                                    index: i,
                                                };
                                            });
                                        }
                                        return [];
                                    },
                                },
                            },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (context) {
                                    var label = context.label || '';
                                    var dataset = context.dataset;
                                    var index = context.dataIndex;
                                    var value = dataset.data[index];

                                    if (label) {
                                        label = dataset.label + ' - ' + label + ': ' + value;
                                    }
                                    return label;
                                }
                            },
                            titleFontSize: 16, // Decreased tooltip title font size
                            bodyFontSize: 14, // Decreased tooltip body font size
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20,
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 1,
                            }
                        }
                    }
                });
                // Initial Registrations Chart
                var initialRegistrationsCtx = document.getElementById('TotalAttendedChart').getContext('2d');
                var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Members', 'Non-Members'],
                        datasets: [{
                            label: 'Total attended',
                            data: [@foreach($participantsAttended as $registration){{ $registration->total_members_attended }},{{ $registration->total_non_members_attended }}  @endforeach],
                            backgroundColor: [
                                '#37a739',
                                '#7fd581',
                                // 'rgba(54, 162, 235, 0.8)',
                                // 'rgba(255, 99, 132, 0.8)',
                                // 'rgba(255, 205, 86, 0.9)',
                            ],
                            borderColor: '#fff',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12, // Decreased legend font size
                                    },
                                    generateLabels: function (chart) {
                                        var data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map(function (label, i) {
                                                var ds = data.datasets[0];
                                                var arc = chart.getDatasetMeta(0).data[i];
                                                var value = ds.data[i];
                                                var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';

                                                return {
                                                    text: label + "\n" + value,
                                                    fillStyle: backgroundColor,
                                                    strokeStyle: '#fff',
                                                    lineWidth: 1,
                                                    hidden: isNaN(ds.data[i]),
                                                    index: i,
                                                };
                                            });
                                        }
                                        return [];
                                    },
                                },
                            },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (context) {
                                    var label = context.label || '';
                                    var dataset = context.dataset;
                                    var index = context.dataIndex;
                                    var value = dataset.data[index];

                                    if (label) {
                                        label = dataset.label + ' - ' + label + ': ' + value;
                                    }
                                    return label;
                                }
                            },
                            titleFontSize: 16, // Decreased tooltip title font size
                            bodyFontSize: 14, // Decreased tooltip body font size
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20,
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 1,
                            }
                        }
                    }
                });


                // Total Initial Registrations
                var totalInitialRegistrations = 0;
                @foreach($initialRegistrations as $registration)
                    totalInitialRegistrations += {{ $registration->total_registrations }};
                @endforeach
                document.getElementById('totalInitialRegistrations').textContent = totalInitialRegistrations;


                // Total Members
                var totalMembers = 0;
                @foreach($initialRegistrations as $registration)
                    totalMembers += {{ $registration->total_members }};
                @endforeach
                document.getElementById('totalMembers').textContent = totalMembers;

                // Total Non-Members
                var totalNonMembers = 0;
                @foreach($initialRegistrations as $registration)
                    totalNonMembers += {{ $registration->total_non_members }};
                @endforeach
                document.getElementById('totalNonMembers').textContent = totalNonMembers;

                // Update total redeemed conference pack
                var totalRedeemedConferencePack = 0;
                @foreach($conferencePackRedeemed as $redeemed)
                    totalRedeemedConferencePack += {{ $redeemed->total_redeemed }};
                @endforeach
                document.getElementById('totalRedeemedConferencePack').textContent = totalRedeemedConferencePack;
                // total attended participants
                var totalAttended = 0;
                @foreach($participantsAttended as $redeemed)
                    totalAttended += {{ $redeemed->total_participants_attended }};
                @endforeach
                document.getElementById('totalAttended').textContent = totalAttended;
                // meal coupons chart
                var totalMeals = 0;
                @foreach($mealCoupon as $redeemed)
                    totalMeals += {{ $redeemed->total_meals }};
                @endforeach
                document.getElementById('totalMeals').textContent = totalMeals;

                var totalMembersAttended = 0;
                @foreach($participantsAttended as $redeemed)
                    totalMembersAttended += {{ $redeemed->total_members_attended }};
                @endforeach
                document.getElementById('totalMembersAttended').textContent = totalMembersAttended;

                var totalMembersAttendedd = 0;
                @foreach($walkinParticipants as $redeemed)
                    totalMembersAttendedd += {{ $redeemed->total_walkin_members }};
                @endforeach
                document.getElementById('totalMembersAttendedd').textContent = totalMembersAttendedd;


                var totalNonMembersAttendedd = 0;
                @foreach($walkinParticipants as $redeemed)
                    totalNonMembersAttendedd += {{ $redeemed->total_walkin_non_members }};
                @endforeach
                document.getElementById('totalNonMembersAttendedd').textContent = totalNonMembersAttendedd;

                var totalAttendedNonMembers = 0;
                @foreach($participantsAttended as $redeemed)
                    totalAttendedNonMembers += {{ $redeemed->total_non_members_attended }};
                @endforeach
                document.getElementById('totalAttendedNonMembers').textContent = totalAttendedNonMembers;

                var totalMeals = 0;
                @foreach($mealCoupon as $redeemed)
                    totalMeals += {{ $redeemed->total_meals }};
                @endforeach
                document.getElementById('totalMeals').textContent = totalMeals;


                // Walkins
                var totalWalkins = 0;
                @foreach($walkinParticipants as $registration)
                    totalWalkins += {{ $registration->total_walkins }};
                @endforeach
                document.getElementById('totalWalkins').textContent = totalWalkins;


                // Total Members
                var totalWalkMembers = 0;
                @foreach($walkinParticipants as $registration)
                    totalWalkinMembers += {{ $registration->total_walkin_members }};
                @endforeach
                document.getElementById('totalWalkMembers').textContent = totalWalkMembers;

                // Total Non-Members
                var totalWalkNonMembers = 0;
                @foreach($walkinParticipants as $registration)
                    totalWalkNonMembers += {{ $registration->total_walkin_non_members }};
                @endforeach
                document.getElementById('totalWalkNonMembers').textContent = totalWalkNonMembers;

            </script>
            <script>
                // Initial Registrations Chart
                var initialRegistrationsCtx = document.getElementById('totalMealsChart').getContext('2d');
                var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Premium', 'Extras'],
                        datasets: [{
                            label: 'Total meals',
                            data: [@foreach($mealCoupon as $registration){{ $registration->total_members_meals }},{{ $registration->total_non_members_meals }}  @endforeach],
                            backgroundColor: [

                                '#fee802',
                                '#fcf9c5'
                                // 'rgba(54, 162, 235, 0.8)',
                                // 'rgba(255, 99, 132, 0.8)',
                                // 'rgba(255, 205, 86, 0.9)',
                            ],
                            borderColor: '#fff',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12, // Decreased legend font size
                                    },
                                    generateLabels: function (chart) {
                                        var data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map(function (label, i) {
                                                var ds = data.datasets[0];
                                                var arc = chart.getDatasetMeta(0).data[i];
                                                var value = ds.data[i];
                                                var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';

                                                return {
                                                    text: label + "\n" + value,
                                                    fillStyle: backgroundColor,
                                                    strokeStyle: '#fff',
                                                    lineWidth: 1,
                                                    hidden: isNaN(ds.data[i]),
                                                    index: i,
                                                };
                                            });
                                        }
                                        return [];
                                    },
                                },
                            },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (context) {
                                    var label = context.label || '';
                                    var dataset = context.dataset;
                                    var index = context.dataIndex;
                                    var value = dataset.data[index];

                                    if (label) {
                                        label = dataset.label + ' - ' + label + ': ' + value;
                                    }
                                    return label;
                                }
                            },
                            titleFontSize: 16, // Decreased tooltip title font size
                            bodyFontSize: 14, // Decreased tooltip body font size
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20,
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 1,
                            }
                        }
                    }
                });

                var initialRegistrationsCtx = document.getElementById('totalMealsRedeemedChart').getContext('2d');
                var hotelNames = {!! json_encode($hotelMealsRedeemed->pluck('hotel_name')) !!};

                var premiumScans = {!! json_encode($hotelMealsRedeemed->pluck('premium_scans')) !!};
                var extrasScans = {!! json_encode($hotelMealsRedeemed->pluck('extras_scans')) !!};

                var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {
                    type: 'bar',
                    data: {
                        labels: hotelNames,
                        datasets: [{
                            label: 'Premium Scans',
                            data: premiumScans,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: '#fff',
                            borderWidth: 1,
                            barThickness: 40,
                            stack: 'hotel' // Added stack option
                        },
                            {
                                label: 'Extras Scans',
                                data: extrasScans,
                                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                                borderColor: '#fff',
                                borderWidth: 1,
                                barThickness: 40,
                                stack: 'hotel' // Added stack option
                            }],
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Meals Redeemed' // Y-axis title
                                },
                                ticks: {
                                    precision: 0 // Show whole numbers only
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Hotel Names' // X-axis title
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 12, // Decreased legend font size
                                    }
                                }
                            },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (context) {
                                    var label = context.label || '';
                                    var dataset = context.dataset;
                                    var index = context.dataIndex;
                                    var value = dataset.data[index];

                                    if (label) {
                                        label = dataset.label + ' - ' + label + ': ' + value;
                                    }
                                    return label;
                                }
                            },
                            titleFontSize: 16, // Decreased tooltip title font size
                            bodyFontSize: 14, // Decreased tooltip body font size
                        },
                        layout: {
                            padding: {
                                left: 20,
                                right: 20,
                                top: 20,
                                bottom: 20,
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 1,
                            }
                        }
                    }
                });


                var totalMembersMeals = 0;
                @foreach($mealCoupon as $redeemed)
                    totalMembersMeals += {{ $redeemed->total_members_meals }};
                @endforeach
                document.getElementById('totalMembersMeals').textContent = totalMembersMeals;

                var totalNonMembersMeals = 0;
                @foreach($mealCoupon as $redeemed)
                    totalNonMembersMeals += {{ $redeemed->total_non_members_meals }};
                @endforeach
                document.getElementById('totalNonMembersMeals').textContent = totalNonMembersMeals;
                // Total Initial Registrations
                var totalInitialRegistrations = 0;
                @foreach($initialRegistrations as $registration)
                    totalInitialRegistrations += {{ $registration->total_registrations }};
                @endforeach
                document.getElementById('totalInitialRegistrations').textContent = totalInitialRegistrations;


                // Total Members
                var totalMembers = 0;
                @foreach($initialRegistrations as $registration)
                    totalMembers += {{ $registration->total_members }};
                @endforeach
                document.getElementById('totalMembers').textContent = totalMembers;

                // Total Non-Members
                var totalNonMembers = 0;
                @foreach($initialRegistrations as $registration)
                    totalNonMembers += {{ $registration->total_non_members }};
                @endforeach
                document.getElementById('totalNonMembers').textContent = totalNonMembers;

                // Update total redeemed conference pack
                var totalRedeemedConferencePack = 0;
                @foreach($walkinParticipants as $redeemed)
                    totalRedeemedConferencePack += {{ $redeemed->total_walkins }};
                @endforeach
                document.getElementById('totalRedeemedConferencePack').textContent = totalRedeemedConferencePack;

                // meal coupons chart
                var totalMeals = 0;
                @foreach($mealCoupon as $redeemed)
                    totalMeals += {{ $redeemed->total_meals }};
                @endforeach
                document.getElementById('totalMeals').textContent = totalMeals;

                // meal coupons chart
                var totalMealsRedeemed = 0;
                @foreach($hotelMealsRedeemed as $redeemed)
                    totalMealsRedeemed += {{ $redeemed->total_meals_redeemed }};
                @endforeach
                document.getElementById('totalMealsRedeemed').textContent = totalMealsRedeemed;

                var totalPremiumScans = 0;
                @foreach($hotelMealsRedeemed as $redeemed)
                    totalPremiumScans += {{ $redeemed->premium_scans }};
                @endforeach
                document.getElementById('totalPremiumScans').textContent = totalPremiumScans;

                var totalExtrasScans = 0;
                @foreach($hotelMealsRedeemed as $redeemed)
                    totalExtrasScans += {{ $redeemed->extras_scans }};
                @endforeach
                document.getElementById('totalExtrasScans').textContent = totalExtrasScans;

            </script>
            @endif
        </div>
        <script>
            function downloadFormPDF() {
                var pages = document.querySelectorAll(".pdf-page");
                var pdfContent = [];

                pages.forEach(function (page, index) {
                    console.log("NEW PAGE")
                    console.log(page)
                    setTimeout(function () {
                        // Wait until the previous page is processed before capturing the next one
                        html2canvas(page, {

                            onclone: function (cloneDoc) {
                                var eventHeading = cloneDoc.querySelector('.pdf-only');
                                eventHeading.style.display = 'block';
                            },

                            useCORS: true,
                            allowTaint: true,
                            scale: 5,
                            scrollX: 0,
                            scrollY: 0,
                            windowWidth: page.offsetWidth,
                            windowHeight: page.offsetHeight,
                            backgroundColor: null // Set background color to null for transparent background
                        }).then(function (canvas) {
                            var ctx = canvas.getContext("2d");
                            ctx.fillStyle = "#ffffff"; // Set the text color to white
                            ctx.shadowColor = "#000000"; // Set the shadow color to black
                            ctx.shadowBlur = 3; // Set the blur radius for the shadow
                            ctx.shadowOffsetX = 0; // Set the X offset for the shadow
                            ctx.shadowOffsetY = 0; // Set the Y offset for the shadow
                            ctx.font = "12px Arial"; // Set the desired font and size

                            var imgData = canvas.toDataURL("image/png");
                            var pdfPage = {
                                image: imgData,
                                width: 595,
                                height: 542
                                // height: 842 // Adjust the height to match the A4 page dimensions
                            };
                            pdfContent.push(pdfPage);
                            if (pdfContent.length === pages.length) {
                                generatePDF();
                            }
                        });
                    }, index * 2000);
                });

                function generatePDF() {
                    var docDefinition = {
                        pageSize: {
                            width: 695,
                            height: 942
                        },
                        pageOrientation: 'portrait', // Set the page orientation to portrait
                        content: pdfContent,
                    };
                    pdfMake.createPdf(docDefinition).download("ICAM_Dashboard.pdf");
                }
            }
        </script>

@endsection









































{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>--}}
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>--}}

{{--<style>--}}


{{--    @media screen and (max-width: 768px) {--}}
{{--        .chart-container {--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--        }--}}

{{--        .chart-card {--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}

{{--        .d-flex.justify-content-center {--}}
{{--            flex-direction: column;--}}
{{--            align-items: center;--}}
{{--            margin-top: 10px;--}}
{{--        }--}}

{{--        .d-none.d-sm-block {--}}
{{--            display: none;--}}
{{--        }--}}

{{--        .chart-container2 {--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--        }--}}

{{--        .chart-card {--}}
{{--            margin-left: 0;--}}
{{--            width: 100%;--}}
{{--        }--}}

{{--        #dayFilterForm {--}}
{{--            text-align: center;--}}
{{--            margin-bottom: 10px;--}}
{{--        }--}}

{{--        #dayFilter {--}}
{{--            margin-right: 10px;--}}
{{--        }--}}

{{--        #totalMealsRedeemedChart {--}}
{{--            height: 320px;--}}
{{--            width: 100%;--}}
{{--        }--}}

{{--        .chart-container2 .chart-card:first-child {--}}
{{--            margin-left: 0;--}}
{{--            width: 100%;--}}
{{--        }--}}

{{--        .chart-container2 .chart-card:last-child {--}}
{{--            margin-left: 0;--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--    }--}}


{{--    body {--}}
{{--        font-family: Arial, sans-serif;--}}
{{--        margin: 0;--}}
{{--        padding: 20px;--}}
{{--        background-color: #f0f8ff; /* Faint blue color */--}}
{{--    }--}}

{{--    .chart-container {--}}
{{--        display: flex;--}}
{{--        /*justify-content: space-between;*/--}}
{{--    }--}}
{{--    .chart-container2 {--}}
{{--        display: flex;--}}
{{--        margin-top: 30px;--}}
{{--        /*justify-content: space-between;*/--}}
{{--    }--}}
{{--    .chart-card {--}}

{{--        margin-bottom: 20px;--}}
{{--        background-color: #fff;--}}
{{--        border-radius: 8px;--}}
{{--        margin: auto;--}}
{{--        /*margin-right: 80px;*/--}}
{{--        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);--}}
{{--    }--}}

{{--    .chart-card h2 {--}}
{{--        font-size: 10px; /* Decreased font size */--}}
{{--        margin: 0 0 16px;--}}
{{--    }--}}

{{--    .chart-card canvas {--}}
{{--        max-width: 100%;--}}
{{--        max-height: 100%;--}}
{{--    }--}}

{{--    .chart-card .total {--}}
{{--        font-size: 10px; /* Decreased font size */--}}
{{--        text-align: center;--}}
{{--    }--}}

{{--    hr {--}}
{{--        height: 2px;--}}
{{--        background-color: #000;--}}
{{--        border: none;--}}
{{--        margin-top: 10px;--}}
{{--        margin-bottom: 10px;--}}
{{--    }--}}

{{--    .total-participants {--}}
{{--        font-size: 54px;--}}
{{--        /*font-weight: bold;*/--}}
{{--        color: #333;--}}
{{--        text-align: center;--}}
{{--    }--}}
{{--    .pdf-page {--}}
{{--        /*display: grid;*/--}}
{{--        /*grid-template-columns: repeat(2, 1fr);*/--}}
{{--        gap: 20px;--}}
{{--        margin-bottom: 40px;--}}
{{--    }--}}

{{--    .list-item h5 {--}}
{{--        font-size: 18px;--}}
{{--        margin-bottom: 10px;--}}
{{--    }--}}

{{--    .list-item .badge {--}}
{{--        display: block;--}}
{{--        width: 35px;--}}
{{--        height: 6px;--}}
{{--        margin: 10px auto;--}}
{{--        border-radius: 3px;--}}
{{--    }--}}

{{--    .text-muted {--}}
{{--        color: #888;--}}
{{--    }--}}


{{--    .dropdowns {--}}
{{--        position: absolute;--}}
{{--        top: 120px; /* Adjust the top distance as needed */--}}
{{--        right: 25px; /* Adjust the right distance as needed */--}}
{{--        display: inline-block;--}}
{{--    }--}}

{{--    .dropdown-content {--}}
{{--        display: none;--}}
{{--        position: absolute;--}}
{{--        right: 0;--}}
{{--        background-color: #f9f9f9;--}}
{{--        min-width: 260px;--}}
{{--        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);--}}
{{--        z-index: 1;--}}
{{--        padding: 10px 0;--}}
{{--        list-style-type: none;--}}
{{--    }--}}

{{--    .dropdown-content a {--}}
{{--        display: block;--}}
{{--        padding: 10px;--}}
{{--        text-decoration: none;--}}
{{--        color: #333;--}}
{{--        transition: background-color 0.3s ease;--}}
{{--    }--}}

{{--    .dropdown-content a:hover {--}}
{{--        background-color: #ddd;--}}
{{--    }--}}

{{--    .dropdowns:hover .dropdown-content {--}}
{{--        display: block;--}}
{{--    }--}}
{{--    .centralize-graph{--}}
{{--        padding: 20px;--}}
{{--        width: 310px;--}}
{{--    }--}}


{{--    /* Added style for the dropdown button */--}}
{{--    .dropbtn {--}}
{{--        background-color: rgb(237, 28, 36); /* Set the background color to the specified RGB value */--}}
{{--        color: #fff;--}}
{{--        padding: 10px;--}}
{{--        border: none;--}}
{{--        cursor: pointer;--}}
{{--    }--}}

{{--    /* Optional style to change the color of the dropdown button on hover */--}}
{{--    .dropbtn:hover {--}}
{{--        background-color: #c71b28; /* Change the background color on hover if needed */--}}
{{--    }--}}
{{--</style>--}}


{{--@extends('layouts.app')--}}

{{--@section('content')--}}


{{--    <div style="display: flex; align-items: center;">--}}
{{--        <button type="button" style="background-color: rgb(237, 28, 36); border-radius: 4px; border: none; color: white; padding: 8px 16px; transition: background-color 0.3s ease;margin-top: 2%; margin-left: 850px;" onclick="downloadFormPDF()">Download Dashboard</button>--}}
{{--        <div class="dropdowns" style="margin-top: 2%; margin-right: 20%;">--}}
{{--            <div class="dropdown">--}}
{{--                <select onchange="location = this.value;" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; background-color: white;">--}}
{{--                    <option selected disabled>Choose an event</option>--}}
{{--                    @foreach(\App\Models\Event::pluck('event_name') as $eventName)--}}
{{--                        <option value="{{ route('event-report4', ['event' => $eventName]) }}">{{ $eventName }}</option>--}}
{{--                    @endforeach--}}
{{--                </select>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}


{{--    <div id="pdfContent" style="display: none;"></div>--}}
{{--    <?php--}}
{{--    $index = 1; // Declare and initialize the variable--}}
{{--    $index++; // Increment the value of $index by 1--}}
{{--    ?>--}}
{{--    @if($index)--}}
{{--        <div class="pdf-page">--}}
{{--            --}}{{--            <h1>{{ $event->event_name }}</h1>--}}

{{--            <h1 style="font-size: 24px;margin-top: -3%; font-weight: bold;">{{ $event->event_name }}</h1>--}}

{{--            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>--}}
{{--            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>--}}

{{--            <script>--}}
{{--                function downloadDashboard() {--}}
{{--                    const filename = 'dashboard.pdf';--}}
{{--                    const element = document.body;--}}
{{--                    html2canvas(element).then((canvas) => {--}}
{{--                        const pdf = new jsPDF('p', 'mm', 'a4');--}}
{{--                        const imageData = canvas.toDataURL('image/png');--}}
{{--                        pdf.addImage(imageData, 'PNG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());--}}
{{--                        pdf.save(filename);--}}
{{--                    });--}}
{{--                }--}}
{{--            </script>--}}

{{--            <br><br>--}}
{{--            <div class="chart-container">--}}
{{--                <div class="chart-card">--}}
{{--                    <div style="background-color: #ec242c; width: 100%; height: 100px; border-radius: 5px; ">--}}
{{--                        --}}{{--                    <div style="background-color: blueviolet; width: 100%; height: 100px; ">--}}
{{--                        <b><h2 style="font-size: 17px; text-align: center; color: white; padding-top: 20px;">Participants expected</h2></b>--}}
{{--                        --}}{{--                        <b><h2 style="font-size: 17px; text-align: center; color: white; padding-top: 20px;">Participants expected</h2></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="totalInitialRegistrations"></h2></b>--}}
{{--                    </div>--}}

{{--                    <canvas id="initialRegistrationsChart" class="centralize-graph"></canvas>--}}
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <li class="ct-series-0 d-flex flex-column mr-4">--}}
{{--                            <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>--}}

{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #aa2c13; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalMembers"></div>--}}
{{--                        </li>--}}
{{--                        <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->--}}
{{--                        <li class="ct-series-1 d-flex flex-column">--}}
{{--                            <h5 class="mb-0" style="font-size: 12px">Non Members</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #fbab62; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalNonMembers"></div>--}}
{{--                        </li>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <br><br><br>--}}
{{--                <div class="chart-card">--}}
{{--                    <div style="background-color: #ec242c;  width: 100%; height: 100px; border-radius: 5px;">--}}
{{--                        <b><h2 style="font-size: 17px;padding-top: 20px; text-align: center; color: white; margin-top: 0px;">Walk-in participants</h2></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="totalRedeemedConferencePack"></h2></b>--}}
{{--                    </div>--}}
{{--                    <canvas id="walkInParticipantsChart" class="centralize-graph"></canvas>--}}
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <li class="ct-series-0 d-flex flex-column mr-4">--}}

{{--                            <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #01949a; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalMembersAttendedd"></div>--}}
{{--                        </li>--}}
{{--                        <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->--}}
{{--                        <li class="ct-series-1 d-flex flex-column">--}}
{{--                            <h5 class="mb-0" style="font-size: 12px">Non Members</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#01dee7; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalNonMembersAttendedd"></div>--}}
{{--                        </li>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br><br><br>--}}
{{--                <div class="chart-card">--}}
{{--                    <div style="background-color:#ec242c;  width: 100%; height: 100px;border-radius: 5px;">--}}
{{--                        <b><h2 style="font-size: 17px;padding-top: 20px; text-align: center; color: white; margin-top: 0px;">Total attended</h2></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="totalAttended"></h2></b>--}}
{{--                    </div>--}}
{{--                    <canvas id="TotalAttendedChart"class="centralize-graph"></canvas>--}}
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <li class="ct-series-0 d-flex flex-column mr-4">--}}
{{--                            <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Members</h5>--}}

{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#37a739 ; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalMembersAttended"></div>--}}
{{--                        </li>--}}
{{--                        <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->--}}
{{--                        <li class="ct-series-1 d-flex flex-column">--}}
{{--                            <h5 class="mb-0" style="font-size: 12px">Non Members</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#7fd581; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalAttendedNonMembers"></div>--}}
{{--                        </li>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <br><br><br>--}}
{{--            <div class="chart-container2">--}}
{{--                <div class="chart-card" style="margin-left: 50px;width: 300px;">--}}
{{--                    <div style="background-color: #ec242c; width: 100%; height: 100px;border-radius: 5px;">--}}
{{--                        <b><h2 style="font-size: 17px;padding-top: 20px; text-align: center; color: white;">Total meals</h2></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="totalMeals"></h2></b>--}}
{{--                    </div>--}}
{{--                    <canvas id="totalMealsChart"class="centralize-graph"></canvas>--}}
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <li class="ct-series-0 d-flex flex-column mr-4">--}}
{{--                            <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Premium</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:#fee802 ; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalMembersMeals"></div>--}}
{{--                        </li>--}}
{{--                        <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->--}}
{{--                        <li class="ct-series-1 d-flex flex-column">--}}
{{--                            <h5 class="mb-0" style="font-size: 12px">Extras</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: #fcf9c5; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalNonMembersMeals"></div>--}}

{{--                        </li>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <br><br><br>--}}

{{--                <div class="chart-card" style="margin-left: 40px">--}}

{{--                    <div style="background-color: #ec242c; width: 100%; height: 100px;border-radius: 5px;">--}}
{{--                        <b><h2 style="font-size: 17px;padding-top: 20px; text-align: center; color: white; margin-top: 0px;">Total meals redeemed</h2 ></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="totalMealsRedeemed"></h2></b>--}}

{{--                    </div>--}}

{{--                    --}}{{--                <form id="dayFilterForm">--}}
{{--                    --}}{{--                    <label for="dayFilter">Select Day:</label>--}}
{{--                    --}}{{--                    <select id="dayFilter" name="dayFilter">--}}
{{--                    --}}{{--                        <option value="all">All</option>--}}
{{--                    --}}{{--                        <option value="Monday">Monday</option>--}}
{{--                    --}}{{--                        <option value="Tuesday">Tuesday</option>--}}
{{--                    --}}{{--                        <option value="Wednesday">Wednesday</option>--}}
{{--                    --}}{{--                        <option value="Thursday">Thursday</option>--}}
{{--                    --}}{{--                        <option value="Friday">Friday</option>--}}
{{--                    --}}{{--                        <option value="Saturday">Saturday</option>--}}
{{--                    --}}{{--                        <option value="Sunday">Sunday</option>--}}
{{--                    --}}{{--                    </select>--}}
{{--                    --}}{{--                    <button type="submit">Apply</button>--}}
{{--                    --}}{{--                </form>--}}
{{--                    <canvas id="totalMealsRedeemedChart" style="--}}
{{--           height: 320px;--}}
{{--           width: 690px;--}}
{{--       "></canvas>--}}
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <li class="ct-series-0 d-flex flex-column mr-4">--}}
{{--                            <h5 class="mb-0" style="padding-right: 30px; font-size: 12px">Premium scans</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:rgba(54, 162, 235, 0.8) ; width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalPremiumScans"></div>--}}
{{--                        </li>--}}
{{--                        <div class="d-none d-sm-block"></div> <!-- Add a hidden div to create space on small screens -->--}}
{{--                        <li class="ct-series-1 d-flex flex-column">--}}
{{--                            <h5 class="mb-0" style="font-size: 12px">Extras scans</h5>--}}
{{--                            <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color:rgba(255, 99, 132, 0.8); width: 30px; height: 6px;"></span>--}}
{{--                            <div class="text-muted" id="totalExtrasScans"></div>--}}
{{--                        </li>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}

{{--            <br><br><br>--}}

{{--            <div class="chart-container2">--}}
{{--                <div class="chart-card" style="margin-left: 20px;width: 1200px;">--}}
{{--                    <div style="background-color: #ec242c; width: 100%; height: 100px;border-radius: 5px;">--}}
{{--                        <b><h2 style="font-size: 17px;padding-top: 20px; text-align: center; color: white; margin-top: 0px;">Conference hall registration</h2></b>--}}
{{--                        <b><h2 class="total-participants" style="font-size: 25px; color: white" id="combinedTotal"></h2></b>--}}



{{--                    </div>--}}
{{--                    <canvas id="lineGraphChart"></canvas>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>--}}
{{--        <script>--}}
{{--            // Initial Registrations Chart--}}
{{--            var initialRegistrationsCtx = document.getElementById('initialRegistrationsChart').getContext('2d');--}}
{{--            var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
{{--                type: 'pie',--}}
{{--                data: {--}}
{{--                    labels: ['Members', 'Non-Members'],--}}
{{--                    datasets: [{--}}
{{--                        label: 'Total Initial Registrations',--}}
{{--                        data: [@foreach($initialRegistrations as $registration){{ $registration->total_members }}, {{ $registration->total_non_members }}, @endforeach],--}}
{{--                        backgroundColor: [--}}
{{--                            // 'rgba(54, 162, 235, 0.8)',--}}
{{--                            '#aa2c13',--}}
{{--                            '#fbab62',--}}
{{--                            // 'rgba(255, 99, 132, 0.8)',--}}
{{--                            // 'rgba(255, 205, 86, 0.9)',--}}
{{--                        ],--}}
{{--                        borderColor: '#fff',--}}
{{--                        borderWidth: 1,--}}
{{--                    }],--}}
{{--                },--}}
{{--                options: {--}}
{{--                    cutout: 0,--}}
{{--                    responsive: true,--}}
{{--                    plugins: {--}}
{{--                        legend: {--}}
{{--                            display: false,--}}
{{--                            position: 'bottom',--}}
{{--                            labels: {--}}
{{--                                font: {--}}
{{--                                    size: 12, // Decreased legend font size--}}
{{--                                },--}}
{{--                                generateLabels: function (chart) {--}}
{{--                                    var data = chart.data;--}}
{{--                                    if (data.labels.length && data.datasets.length) {--}}
{{--                                        return data.labels.map(function (label, i) {--}}
{{--                                            var ds = data.datasets[0];--}}
{{--                                            var arc = chart.getDatasetMeta(0).data[i];--}}
{{--                                            var value = ds.data[i];--}}
{{--                                            var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';--}}

{{--                                            return {--}}
{{--                                                text: label + "\n" + value,--}}
{{--                                                fillStyle: backgroundColor,--}}
{{--                                                strokeStyle: '#fff',--}}
{{--                                                lineWidth: 1,--}}
{{--                                                hidden: isNaN(ds.data[i]),--}}
{{--                                                index: i,--}}
{{--                                            };--}}
{{--                                        });--}}
{{--                                    }--}}
{{--                                    return [];--}}
{{--                                },--}}
{{--                            },--}}
{{--                        },--}}
{{--                    },--}}
{{--                    tooltips: {--}}
{{--                        callbacks: {--}}
{{--                            label: function (context) {--}}
{{--                                var label = context.label || '';--}}
{{--                                var dataset = context.dataset;--}}
{{--                                var index = context.dataIndex;--}}
{{--                                var value = dataset.data[index];--}}

{{--                                if (label) {--}}
{{--                                    label = dataset.label + ' - ' + label + ': ' + value;--}}
{{--                                }--}}
{{--                                return label;--}}
{{--                            }--}}
{{--                        },--}}
{{--                        titleFontSize: 16, // Decreased tooltip title font size--}}
{{--                        bodyFontSize: 14, // Decreased tooltip body font size--}}
{{--                    },--}}
{{--                    layout: {--}}
{{--                        padding: {--}}
{{--                            left: 20,--}}
{{--                            right: 20,--}}
{{--                            top: 20,--}}
{{--                            bottom: 20,--}}
{{--                        }--}}
{{--                    },--}}
{{--                    elements: {--}}
{{--                        arc: {--}}
{{--                            borderWidth: 1,--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}



{{--            // Initial Registrations Chart--}}
{{--            var initialRegistrationsCtx = document.getElementById('walkInParticipantsChart').getContext('2d');--}}
{{--            var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
{{--                type: 'pie',--}}
{{--                data: {--}}
{{--                    labels: ['Members', 'Non-Members'],--}}
{{--                    datasets: [{--}}
{{--                        label: 'Total Walkins registration',--}}
{{--                        data: [@foreach($walkinParticipants as $registration){{ $registration->total_walkin_members }},{{ $registration->total_walkin_non_members }}  @endforeach],--}}
{{--                        backgroundColor: [--}}
{{--                            //'rgba(54, 162, 235, 0.8)',--}}
{{--                            //'rgba(255, 99, 132, 0.8)',--}}
{{--                            //'rgba(255, 205, 86, 0.9)',--}}
{{--                            '#01949a',--}}
{{--                            '#01dee7'--}}
{{--                        ],--}}
{{--                        borderColor: '#fff',--}}
{{--                        borderWidth: 1,--}}
{{--                    }],--}}
{{--                },--}}
{{--                options: {--}}
{{--                    responsive: true,--}}
{{--                    plugins: {--}}
{{--                        legend: {--}}
{{--                            display: false,--}}
{{--                            position: 'bottom',--}}
{{--                            labels: {--}}
{{--                                font: {--}}
{{--                                    size: 12, // Decreased legend font size--}}
{{--                                },--}}
{{--                                generateLabels: function (chart) {--}}
{{--                                    var data = chart.data;--}}
{{--                                    if (data.labels.length && data.datasets.length) {--}}
{{--                                        return data.labels.map(function (label, i) {--}}
{{--                                            var ds = data.datasets[0];--}}
{{--                                            var arc = chart.getDatasetMeta(0).data[i];--}}
{{--                                            var value = ds.data[i];--}}
{{--                                            var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';--}}

{{--                                            return {--}}
{{--                                                text: label + "\n" + value,--}}
{{--                                                fillStyle: backgroundColor,--}}
{{--                                                strokeStyle: '#fff',--}}
{{--                                                lineWidth: 1,--}}
{{--                                                hidden: isNaN(ds.data[i]),--}}
{{--                                                index: i,--}}
{{--                                            };--}}
{{--                                        });--}}
{{--                                    }--}}
{{--                                    return [];--}}
{{--                                },--}}
{{--                            },--}}
{{--                        },--}}
{{--                    },--}}
{{--                    tooltips: {--}}
{{--                        callbacks: {--}}
{{--                            label: function (context) {--}}
{{--                                var label = context.label || '';--}}
{{--                                var dataset = context.dataset;--}}
{{--                                var index = context.dataIndex;--}}
{{--                                var value = dataset.data[index];--}}

{{--                                if (label) {--}}
{{--                                    label = dataset.label + ' - ' + label + ': ' + value;--}}
{{--                                }--}}
{{--                                return label;--}}
{{--                            }--}}
{{--                        },--}}
{{--                        titleFontSize: 16, // Decreased tooltip title font size--}}
{{--                        bodyFontSize: 14, // Decreased tooltip body font size--}}
{{--                    },--}}
{{--                    layout: {--}}
{{--                        padding: {--}}
{{--                            left: 20,--}}
{{--                            right: 20,--}}
{{--                            top: 20,--}}
{{--                            bottom: 20,--}}
{{--                        }--}}
{{--                    },--}}
{{--                    elements: {--}}
{{--                        arc: {--}}
{{--                            borderWidth: 1,--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}
{{--            // Initial Registrations Chart--}}
{{--            var initialRegistrationsCtx = document.getElementById('TotalAttendedChart').getContext('2d');--}}
{{--            var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
{{--                type: 'pie',--}}
{{--                data: {--}}
{{--                    labels: ['Members', 'Non-Members'],--}}
{{--                    datasets: [{--}}
{{--                        label: 'Total attended',--}}
{{--                        data: [@foreach($participantsAttended as $registration){{ $registration->total_members_attended }},{{ $registration->total_non_members_attended }}  @endforeach],--}}
{{--                        backgroundColor: [--}}
{{--                            '#37a739',--}}
{{--                            '#7fd581',--}}
{{--                            // 'rgba(54, 162, 235, 0.8)',--}}
{{--                            // 'rgba(255, 99, 132, 0.8)',--}}
{{--                            // 'rgba(255, 205, 86, 0.9)',--}}
{{--                        ],--}}
{{--                        borderColor: '#fff',--}}
{{--                        borderWidth: 1,--}}
{{--                    }],--}}
{{--                },--}}
{{--                options: {--}}
{{--                    responsive: true,--}}
{{--                    plugins: {--}}
{{--                        legend: {--}}
{{--                            display: false,--}}
{{--                            position: 'bottom',--}}
{{--                            labels: {--}}
{{--                                font: {--}}
{{--                                    size: 12, // Decreased legend font size--}}
{{--                                },--}}
{{--                                generateLabels: function (chart) {--}}
{{--                                    var data = chart.data;--}}
{{--                                    if (data.labels.length && data.datasets.length) {--}}
{{--                                        return data.labels.map(function (label, i) {--}}
{{--                                            var ds = data.datasets[0];--}}
{{--                                            var arc = chart.getDatasetMeta(0).data[i];--}}
{{--                                            var value = ds.data[i];--}}
{{--                                            var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';--}}

{{--                                            return {--}}
{{--                                                text: label + "\n" + value,--}}
{{--                                                fillStyle: backgroundColor,--}}
{{--                                                strokeStyle: '#fff',--}}
{{--                                                lineWidth: 1,--}}
{{--                                                hidden: isNaN(ds.data[i]),--}}
{{--                                                index: i,--}}
{{--                                            };--}}
{{--                                        });--}}
{{--                                    }--}}
{{--                                    return [];--}}
{{--                                },--}}
{{--                            },--}}
{{--                        },--}}
{{--                    },--}}
{{--                    tooltips: {--}}
{{--                        callbacks: {--}}
{{--                            label: function (context) {--}}
{{--                                var label = context.label || '';--}}
{{--                                var dataset = context.dataset;--}}
{{--                                var index = context.dataIndex;--}}
{{--                                var value = dataset.data[index];--}}

{{--                                if (label) {--}}
{{--                                    label = dataset.label + ' - ' + label + ': ' + value;--}}
{{--                                }--}}
{{--                                return label;--}}
{{--                            }--}}
{{--                        },--}}
{{--                        titleFontSize: 16, // Decreased tooltip title font size--}}
{{--                        bodyFontSize: 14, // Decreased tooltip body font size--}}
{{--                    },--}}
{{--                    layout: {--}}
{{--                        padding: {--}}
{{--                            left: 20,--}}
{{--                            right: 20,--}}
{{--                            top: 20,--}}
{{--                            bottom: 20,--}}
{{--                        }--}}
{{--                    },--}}
{{--                    elements: {--}}
{{--                        arc: {--}}
{{--                            borderWidth: 1,--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}


{{--            // Total Initial Registrations--}}
{{--            var totalInitialRegistrations = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalInitialRegistrations += {{ $registration->total_registrations }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalInitialRegistrations').textContent = totalInitialRegistrations;--}}


{{--            // Total Members--}}
{{--            var totalMembers = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalMembers += {{ $registration->total_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMembers').textContent = totalMembers;--}}

{{--            // Total Non-Members--}}
{{--            var totalNonMembers = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalNonMembers += {{ $registration->total_non_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalNonMembers').textContent = totalNonMembers;--}}

{{--            // Update total redeemed conference pack--}}
{{--            var totalRedeemedConferencePack = 0;--}}
{{--            @foreach($conferencePackRedeemed as $redeemed)--}}
{{--                totalRedeemedConferencePack += {{ $redeemed->total_redeemed }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalRedeemedConferencePack').textContent = totalRedeemedConferencePack;--}}
{{--            // total attended participants--}}
{{--            var totalAttended = 0;--}}
{{--            @foreach($participantsAttended as $redeemed)--}}
{{--                totalAttended += {{ $redeemed->total_participants_attended }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalAttended').textContent = totalAttended;--}}
{{--            // meal coupons chart--}}
{{--            var totalMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalMeals += {{ $redeemed->total_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMeals').textContent = totalMeals;--}}

{{--            var totalMembersAttended = 0;--}}
{{--            @foreach($participantsAttended as $redeemed)--}}
{{--                totalMembersAttended += {{ $redeemed->total_members_attended }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMembersAttended').textContent = totalMembersAttended;--}}

{{--            var totalMembersAttendedd = 0;--}}
{{--            @foreach($walkinParticipants as $redeemed)--}}
{{--                totalMembersAttendedd += {{ $redeemed->total_walkin_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMembersAttendedd').textContent = totalMembersAttendedd;--}}


{{--            var totalNonMembersAttendedd = 0;--}}
{{--            @foreach($walkinParticipants as $redeemed)--}}
{{--                totalNonMembersAttendedd += {{ $redeemed->total_walkin_non_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalNonMembersAttendedd').textContent = totalNonMembersAttendedd;--}}

{{--            var totalAttendedNonMembers = 0;--}}
{{--            @foreach($participantsAttended as $redeemed)--}}
{{--                totalAttendedNonMembers += {{ $redeemed->total_non_members_attended }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalAttendedNonMembers').textContent = totalAttendedNonMembers;--}}

{{--            var totalMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalMeals += {{ $redeemed->total_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMeals').textContent = totalMeals;--}}


{{--            // Walkins--}}
{{--            var totalWalkins = 0;--}}
{{--            @foreach($walkinParticipants as $registration)--}}
{{--                totalWalkins += {{ $registration->total_walkins }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalWalkins').textContent = totalWalkins;--}}


{{--            // Total Members--}}
{{--            var totalWalkMembers = 0;--}}
{{--            @foreach($walkinParticipants as $registration)--}}
{{--                totalWalkinMembers += {{ $registration->total_walkin_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalWalkMembers').textContent = totalWalkMembers;--}}

{{--            // Total Non-Members--}}
{{--            var totalWalkNonMembers = 0;--}}
{{--            @foreach($walkinParticipants as $registration)--}}
{{--                totalWalkNonMembers += {{ $registration->total_walkin_non_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalWalkNonMembers').textContent = totalWalkNonMembers;--}}

{{--        </script>--}}

{{--        <script>--}}
{{--            // Initial Registrations Chart--}}
{{--            var initialRegistrationsCtx = document.getElementById('totalMealsChart').getContext('2d');--}}
{{--            var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
{{--                type: 'pie',--}}
{{--                data: {--}}
{{--                    labels: ['Premium', 'Extras'],--}}
{{--                    datasets: [{--}}
{{--                        label: 'Total meals',--}}
{{--                        data: [@foreach($mealCoupon as $registration){{ $registration->total_members_meals }},{{ $registration->total_non_members_meals }}  @endforeach],--}}
{{--                        backgroundColor: [--}}

{{--                            '#fee802',--}}
{{--                            '#fcf9c5'--}}
{{--                            // 'rgba(54, 162, 235, 0.8)',--}}
{{--                            // 'rgba(255, 99, 132, 0.8)',--}}
{{--                            // 'rgba(255, 205, 86, 0.9)',--}}
{{--                        ],--}}
{{--                        borderColor: '#fff',--}}
{{--                        borderWidth: 1,--}}
{{--                    }],--}}
{{--                },--}}
{{--                options: {--}}
{{--                    responsive: true,--}}
{{--                    plugins: {--}}
{{--                        legend: {--}}
{{--                            display: false,--}}
{{--                            position: 'bottom',--}}
{{--                            labels: {--}}
{{--                                font: {--}}
{{--                                    size: 12, // Decreased legend font size--}}
{{--                                },--}}
{{--                                generateLabels: function (chart) {--}}
{{--                                    var data = chart.data;--}}
{{--                                    if (data.labels.length && data.datasets.length) {--}}
{{--                                        return data.labels.map(function (label, i) {--}}
{{--                                            var ds = data.datasets[0];--}}
{{--                                            var arc = chart.getDatasetMeta(0).data[i];--}}
{{--                                            var value = ds.data[i];--}}
{{--                                            var backgroundColor = (i === 0) ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)';--}}

{{--                                            return {--}}
{{--                                                text: label + "\n" + value,--}}
{{--                                                fillStyle: backgroundColor,--}}
{{--                                                strokeStyle: '#fff',--}}
{{--                                                lineWidth: 1,--}}
{{--                                                hidden: isNaN(ds.data[i]),--}}
{{--                                                index: i,--}}
{{--                                            };--}}
{{--                                        });--}}
{{--                                    }--}}
{{--                                    return [];--}}
{{--                                },--}}
{{--                            },--}}
{{--                        },--}}
{{--                    },--}}
{{--                    tooltips: {--}}
{{--                        callbacks: {--}}
{{--                            label: function (context) {--}}
{{--                                var label = context.label || '';--}}
{{--                                var dataset = context.dataset;--}}
{{--                                var index = context.dataIndex;--}}
{{--                                var value = dataset.data[index];--}}

{{--                                if (label) {--}}
{{--                                    label = dataset.label + ' - ' + label + ': ' + value;--}}
{{--                                }--}}
{{--                                return label;--}}
{{--                            }--}}
{{--                        },--}}
{{--                        titleFontSize: 16, // Decreased tooltip title font size--}}
{{--                        bodyFontSize: 14, // Decreased tooltip body font size--}}
{{--                    },--}}
{{--                    layout: {--}}
{{--                        padding: {--}}
{{--                            left: 20,--}}
{{--                            right: 20,--}}
{{--                            top: 20,--}}
{{--                            bottom: 20,--}}
{{--                        }--}}
{{--                    },--}}
{{--                    elements: {--}}
{{--                        arc: {--}}
{{--                            borderWidth: 1,--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}

{{--            var initialRegistrationsCtx = document.getElementById('totalMealsRedeemedChart').getContext('2d');--}}
{{--            var hotelNames = {!! json_encode($hotelMealsRedeemed->pluck('hotel_name')) !!};--}}

{{--            var premiumScans = {!! json_encode($hotelMealsRedeemed->pluck('premium_scans')) !!};--}}
{{--            var extrasScans = {!! json_encode($hotelMealsRedeemed->pluck('extras_scans')) !!};--}}

{{--            var initialRegistrationsChart = new Chart(initialRegistrationsCtx, {--}}
{{--                type: 'bar',--}}
{{--                data: {--}}
{{--                    labels: hotelNames,--}}
{{--                    datasets: [{--}}
{{--                        label: 'Premium Scans',--}}
{{--                        data: premiumScans,--}}
{{--                        backgroundColor: 'rgba(54, 162, 235, 0.8)',--}}
{{--                        borderColor: '#fff',--}}
{{--                        borderWidth: 1,--}}
{{--                        barThickness: 40,--}}
{{--                        stack: 'hotel' // Added stack option--}}
{{--                    },--}}
{{--                        {--}}
{{--                            label: 'Extras Scans',--}}
{{--                            data: extrasScans,--}}
{{--                            backgroundColor: 'rgba(255, 99, 132, 0.8)',--}}
{{--                            borderColor: '#fff',--}}
{{--                            borderWidth: 1,--}}
{{--                            barThickness: 40,--}}
{{--                            stack: 'hotel' // Added stack option--}}
{{--                        }],--}}
{{--                },--}}
{{--                options: {--}}
{{--                    responsive: true,--}}
{{--                    indexAxis: 'y',--}}
{{--                    scales: {--}}
{{--                        x: {--}}
{{--                            title: {--}}
{{--                                display: true,--}}
{{--                                text: 'Meals Redeemed' // Y-axis title--}}
{{--                            },--}}
{{--                            ticks: {--}}
{{--                                precision: 0 // Show whole numbers only--}}
{{--                            }--}}
{{--                        },--}}
{{--                        y: {--}}
{{--                            title: {--}}
{{--                                display: true,--}}
{{--                                text: 'Hotel Names' // X-axis title--}}
{{--                            }--}}
{{--                        }--}}
{{--                    },--}}
{{--                    plugins: {--}}
{{--                        legend: {--}}
{{--                            display: false,--}}
{{--                            position: 'bottom',--}}
{{--                            labels: {--}}
{{--                                font: {--}}
{{--                                    size: 12, // Decreased legend font size--}}
{{--                                }--}}
{{--                            }--}}
{{--                        },--}}
{{--                    },--}}
{{--                    tooltips: {--}}
{{--                        callbacks: {--}}
{{--                            label: function (context) {--}}
{{--                                var label = context.label || '';--}}
{{--                                var dataset = context.dataset;--}}
{{--                                var index = context.dataIndex;--}}
{{--                                var value = dataset.data[index];--}}

{{--                                if (label) {--}}
{{--                                    label = dataset.label + ' - ' + label + ': ' + value;--}}
{{--                                }--}}
{{--                                return label;--}}
{{--                            }--}}
{{--                        },--}}
{{--                        titleFontSize: 16, // Decreased tooltip title font size--}}
{{--                        bodyFontSize: 14, // Decreased tooltip body font size--}}
{{--                    },--}}
{{--                    layout: {--}}
{{--                        padding: {--}}
{{--                            left: 20,--}}
{{--                            right: 20,--}}
{{--                            top: 20,--}}
{{--                            bottom: 20,--}}
{{--                        }--}}
{{--                    },--}}
{{--                    elements: {--}}
{{--                        arc: {--}}
{{--                            borderWidth: 1,--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}


{{--            var totalMembersMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalMembersMeals += {{ $redeemed->total_members_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMembersMeals').textContent = totalMembersMeals;--}}

{{--            var totalNonMembersMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalNonMembersMeals += {{ $redeemed->total_non_members_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalNonMembersMeals').textContent = totalNonMembersMeals;--}}
{{--            // Total Initial Registrations--}}
{{--            var totalInitialRegistrations = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalInitialRegistrations += {{ $registration->total_registrations }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalInitialRegistrations').textContent = totalInitialRegistrations;--}}


{{--            // Total Members--}}
{{--            var totalMembers = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalMembers += {{ $registration->total_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMembers').textContent = totalMembers;--}}

{{--            // Total Non-Members--}}
{{--            var totalNonMembers = 0;--}}
{{--            @foreach($initialRegistrations as $registration)--}}
{{--                totalNonMembers += {{ $registration->total_non_members }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalNonMembers').textContent = totalNonMembers;--}}

{{--            // Update total redeemed conference pack--}}
{{--            var totalRedeemedConferencePack = 0;--}}
{{--            @foreach($walkinParticipants as $redeemed)--}}
{{--                totalRedeemedConferencePack += {{ $redeemed->total_walkins }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalRedeemedConferencePack').textContent = totalRedeemedConferencePack;--}}

{{--            // meal coupons chart--}}
{{--            var totalMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalMeals += {{ $redeemed->total_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMeals').textContent = totalMeals;--}}

{{--            // meal coupons chart--}}
{{--            var totalMealsRedeemed = 0;--}}
{{--            @foreach($hotelMealsRedeemed as $redeemed)--}}
{{--                totalMealsRedeemed += {{ $redeemed->total_meals_redeemed }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMealsRedeemed').textContent = totalMealsRedeemed;--}}

{{--            var totalPremiumScans = 0;--}}
{{--            @foreach($hotelMealsRedeemed as $redeemed)--}}
{{--                totalPremiumScans += {{ $redeemed->premium_scans }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalPremiumScans').textContent = totalPremiumScans;--}}

{{--            var totalExtrasScans = 0;--}}
{{--            @foreach($hotelMealsRedeemed as $redeemed)--}}
{{--                totalExtrasScans += {{ $redeemed->extras_scans }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalExtrasScans').textContent = totalExtrasScans;--}}

{{--        </script>--}}

{{--        <script>--}}









{{--            // Retrieve the data from the controller--}}
{{--            var membersDataByDateTime = {!! json_encode($membersDataByDateTime) !!};--}}
{{--            var nonMembersDataByDateTime = {!! json_encode($nonMembersDataByDateTime) !!};--}}
{{--            var allDataByDateTime = {!! json_encode($allDataByDateTime) !!};--}}
{{--            var totals = {!! json_encode($totals) !!};--}}



{{--            // Combine all dates and times with registrations and session types for each line--}}
{{--            var memberData = [];--}}
{{--            Object.keys(membersDataByDateTime).forEach(date => {--}}
{{--                membersDataByDateTime[date].forEach(data => {--}}
{{--                    memberData.push({--}}
{{--                        datetime: data.datetime,--}}
{{--                        registrations: data.registrations,--}}
{{--                        type: 'Members' // Set the session type explicitly to 'Members'--}}
{{--                    });--}}
{{--                });--}}
{{--            });--}}

{{--            var nonMemberData = [];--}}
{{--            Object.keys(nonMembersDataByDateTime).forEach(date => {--}}
{{--                nonMembersDataByDateTime[date].forEach(data => {--}}
{{--                    nonMemberData.push({--}}
{{--                        datetime: data.datetime,--}}
{{--                        registrations: data.registrations,--}}
{{--                        type: 'Non-Members' // Set the session type explicitly to 'Non-Members'--}}
{{--                    });--}}
{{--                });--}}
{{--            });--}}

{{--            var allData = [];--}}
{{--            Object.keys(allDataByDateTime).forEach(date => {--}}
{{--                allDataByDateTime[date].forEach(data => {--}}
{{--                    allData.push({--}}
{{--                        datetime: data.datetime,--}}
{{--                        registrations: data.registrations,--}}
{{--                        type: 'All' // Set the session type explicitly to 'All'--}}
{{--                    });--}}
{{--                });--}}
{{--            });--}}

{{--            // Create a Set to store unique dates and times--}}
{{--            var dateTimeSet = new Set();--}}

{{--            // Add all dates and times to the Set--}}
{{--            memberData.forEach(data => {--}}
{{--                dateTimeSet.add(data.datetime);--}}
{{--            });--}}

{{--            nonMemberData.forEach(data => {--}}
{{--                dateTimeSet.add(data.datetime);--}}
{{--            });--}}

{{--            allData.forEach(data => {--}}
{{--                dateTimeSet.add(data.datetime);--}}
{{--            });--}}

{{--            // Convert the Set to an array--}}
{{--            var dateTimeArray = Array.from(dateTimeSet);--}}

{{--            // Sort the array in ascending order--}}
{{--            dateTimeArray.sort((a, b) => new Date(a) - new Date(b));--}}

{{--            // Extract the formatted date and time strings--}}
{{--            var memberLabels = dateTimeArray;--}}
{{--            var memberRegistrations = memberLabels.map(datetime => {--}}
{{--                var data = memberData.find(item => item.datetime === datetime);--}}
{{--                return data ? data.registrations : null;--}}
{{--            });--}}

{{--            var nonMemberLabels = memberLabels;--}}
{{--            var nonMemberRegistrations = nonMemberLabels.map(datetime => {--}}
{{--                var data = nonMemberData.find(item => item.datetime === datetime);--}}
{{--                return data ? data.registrations : null;--}}
{{--            });--}}

{{--            var allLabels = memberLabels;--}}
{{--            var allRegistrations = allLabels.map(datetime => {--}}
{{--                var data = allData.find(item => item.datetime === datetime);--}}
{{--                return data ? data.registrations : null;--}}
{{--            });--}}

{{--            // Create the line chart--}}
{{--            var ctx = document.getElementById('lineGraphChart').getContext('2d');--}}
{{--            new Chart(ctx, {--}}
{{--                type: 'line',--}}
{{--                data: {--}}
{{--                    labels: memberLabels,--}}
{{--                    datasets: [--}}
{{--                        {--}}
{{--                            label: 'Members',--}}
{{--                            data: memberRegistrations,--}}
{{--                            borderColor: 'blue',--}}
{{--                            fill: false,--}}
{{--                            spanGaps: true--}}
{{--                        },--}}
{{--                        {--}}
{{--                            label: 'Non-Members',--}}
{{--                            data: nonMemberRegistrations,--}}
{{--                            borderColor: 'red',--}}
{{--                            fill: false,--}}
{{--                            spanGaps: true--}}
{{--                        },--}}
{{--                        {--}}
{{--                            label: 'All',--}}
{{--                            data: allRegistrations,--}}
{{--                            borderColor: 'green',--}}
{{--                            fill: false,--}}
{{--                            spanGaps: true--}}
{{--                        }--}}
{{--                    ]--}}
{{--                },--}}
{{--                options: {--}}
{{--                    scales: {--}}
{{--                        x: {--}}
{{--                            display: true,--}}
{{--                            title: {--}}
{{--                                display: true,--}}
{{--                                text: 'Date and Time'--}}
{{--                            }--}}
{{--                        },--}}
{{--                        y: {--}}
{{--                            title: {--}}
{{--                                display: true,--}}
{{--                                text: 'Registration Number' // Y-axis title--}}
{{--                            },--}}
{{--                            ticks: {--}}
{{--                                precision: 0 // Show whole numbers only--}}
{{--                            }--}}
{{--                        },--}}

{{--                    },--}}
{{--                    plugins: {--}}
{{--                        title: {--}}
{{--                            display: true,--}}
{{--                            text: 'Event Session Registrations'--}}
{{--                        },--}}
{{--                        tooltips: {--}}
{{--                            mode: 'nearest',--}}
{{--                            intersect: false,--}}
{{--                            callbacks: {--}}
{{--                                title: function(tooltipItems) {--}}
{{--                                    var tooltipItem = tooltipItems[0];--}}
{{--                                    return tooltipItem.label;--}}
{{--                                },--}}
{{--                                label: function(tooltipItem, data) {--}}
{{--                                    var datasetIndex = tooltipItem.datasetIndex;--}}
{{--                                    var index = tooltipItem.dataIndex;--}}
{{--                                    var datasetLabel = data.datasets[datasetIndex].label || '';--}}
{{--                                    var value = tooltipItem.formattedValue;--}}

{{--                                    // Get the corresponding session type and registration--}}
{{--                                    var sessionType = null;--}}
{{--                                    var registration = null;--}}
{{--                                    if (datasetIndex === 0) {--}}
{{--                                        sessionType = 'Members'; // Set the session type explicitly to 'Members'--}}
{{--                                        registration = membersDataByDateTime[memberLabels[index]][0].registrations;--}}
{{--                                    } else if (datasetIndex === 1) {--}}
{{--                                        sessionType = 'Non-Members'; // Set the session type explicitly to 'Non-Members'--}}
{{--                                        registration = nonMembersDataByDateTime[memberLabels[index]][0].registrations;--}}
{{--                                    } else if (datasetIndex === 2) {--}}
{{--                                        sessionType = 'All'; // Set the session type explicitly to 'All'--}}
{{--                                        registration = allDataByDateTime[memberLabels[index]][0].registrations;--}}
{{--                                    }--}}

{{--                                    return datasetLabel + ': ' + value + ', Session Type: ' + sessionType + ', Registrations: ' + registration;--}}
{{--                                }--}}
{{--                            }--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}















{{--            // Calculate the combined total--}}
{{--            var combinedTotal = {{ $totals['Members'] }} + {{ $totals['NonMembers'] }} + {{ $totals['All'] }};--}}

{{--            // Set the combined total value in the HTML element--}}
{{--            document.getElementById('combinedTotal').textContent = combinedTotal;--}}


{{--            // meal coupons chart--}}
{{--            var totalMeals = 0;--}}
{{--            @foreach($mealCoupon as $redeemed)--}}
{{--                totalMeals += {{ $redeemed->total_meals }};--}}
{{--            @endforeach--}}
{{--            document.getElementById('totalMeals').textContent = totalMeals;--}}

{{--            --}}{{--// meal coupons chart--}}
{{--            --}}{{--var totalMealsRedeemed = 0;--}}
{{--            --}}{{--@foreach($hotelMealsRedeemed as $redeemed)--}}
{{--            --}}{{--    totalMealsRedeemed += {{ $redeemed->hotel_meals_redeemed }};--}}
{{--            --}}{{--@endforeach--}}
{{--            --}}{{--document.getElementById('totalMealsRedeemed').textContent = totalMealsRedeemed;--}}

{{--            --}}{{--var totalMealsRedeemed = 0;--}}
{{--            --}}{{--@foreach($hotelMealsRedeemed as $redeemed)--}}
{{--            --}}{{--    totalMealsRedeemed += {{ $redeemed->hotel_meals_redeemed }};--}}
{{--            --}}{{--@endforeach--}}
{{--            --}}{{--document.getElementById('totalMealsRedeemed').textContent = totalMealsRedeemed;--}}

{{--        </script>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--    <script>--}}
{{--        function downloadFormPDF() {--}}
{{--            var pages = document.querySelectorAll(".pdf-page");--}}
{{--            var pdfContent = [];--}}

{{--            pages.forEach(function (page, index) {--}}
{{--                console.log("NEW PAGE")--}}
{{--                console.log(page)--}}
{{--                setTimeout(function () {--}}
{{--                    // Wait until the previous page is processed before capturing the next one--}}
{{--                    html2canvas(page, {--}}
{{--                        useCORS: true,--}}
{{--                        allowTaint: true,--}}
{{--                        scale: 2,--}}
{{--                        scrollX: 0,--}}
{{--                        scrollY: 0,--}}
{{--                        windowWidth: page.offsetWidth,--}}
{{--                        windowHeight: page.offsetHeight,--}}
{{--                        backgroundColor: null // Set background color to null for transparent background--}}
{{--                    }).then(function (canvas) {--}}
{{--                        var ctx = canvas.getContext("2d");--}}
{{--                        ctx.fillStyle = "#ffffff"; // Set the text color to white--}}
{{--                        ctx.shadowColor = "#000000"; // Set the shadow color to black--}}
{{--                        ctx.shadowBlur = 3; // Set the blur radius for the shadow--}}
{{--                        ctx.shadowOffsetX = 0; // Set the X offset for the shadow--}}
{{--                        ctx.shadowOffsetY = 0; // Set the Y offset for the shadow--}}
{{--                        ctx.font = "12px Arial"; // Set the desired font and size--}}

{{--                        var imgData = canvas.toDataURL("image/png");--}}
{{--                        var pdfPage = {--}}
{{--                            image: imgData,--}}
{{--                            width: 595,--}}
{{--                            height: 842 // Adjust the height to match the A4 page dimensions--}}
{{--                        };--}}
{{--                        pdfContent.push(pdfPage);--}}
{{--                        if (pdfContent.length === pages.length) {--}}
{{--                            generatePDF();--}}
{{--                        }--}}
{{--                    });--}}
{{--                }, index * 2000);--}}
{{--            });--}}

{{--            function generatePDF() {--}}
{{--                var docDefinition = {--}}
{{--                    pageSize: {--}}
{{--                        width: 695,--}}
{{--                        height: 942--}}
{{--                    },--}}
{{--                    pageOrientation: 'portrait', // Set the page orientation to portrait--}}
{{--                    content: pdfContent,--}}
{{--                };--}}
{{--                pdfMake.createPdf(docDefinition).download("ICAM_Dashboard.pdf");--}}
{{--            }--}}
{{--        }--}}
{{--    </script>--}}

{{--@endsection--}}
