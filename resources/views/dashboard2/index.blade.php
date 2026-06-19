@extends('layouts.app')

@section('title', env('APP_NAME').": Dashboard")

@section('vendor-css')
    {{-- add css links used in current page--}}
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/apex-charts/apex-charts.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/css/pages/card-analytics.css" />
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}

@endsection

@section('content')
    <div class="row">

        <!-- Vehicle Statistics -->
        <div class="col-12 col-lg-6 col-xl-6 col-xxl-5 order-lg-0 order-1 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Vehicle Statistics</h5>
                </div>
                <div class="card-body row gap-md-0 gap-4">
                    <div class="col-md-5">
                        <h1 class="mb-0 text-nowrap">{{$vehiclesTotal}}</h1>
                        <div id="availableVehiclesPercentage" class="ms-n3"></div>
                    </div>
                    <div class="col-md-7 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex flex-column">
                                <small class="text-muted">Available</small>
                                <span class="fw-semibold"> {{$vehiclesAvailable}}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Hired</small>
                                <span class="fw-semibold"> {{$vehiclesHired}}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">On Repair</small>
                                <span class="fw-semibold"> {{$vehiclesOnRepair}}</span>
                            </div>
                        </div>
                        <div class="progress-wrapper mb-4">
                            <div class="mb-3">
                                <small class="text-muted">Available</small>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style="height:8px">
                                        <div class="progress-bar bg-primary" style="width: {{$vehiclesAvailablePercentage}}%" role="progressbar" aria-valuenow="{{$vehiclesAvailablePercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{$vehiclesAvailablePercentage}}%</small>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted">Hired</small>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style=" height:8px">
                                        <div class="progress-bar bg-primary" style="width: {{$vehiclesHiredPercentage}}%" role="progressbar" aria-valuenow="{{$vehiclesHiredPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{$vehiclesHiredPercentage}}%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Vehicle Statistics-->

        <!-- Vehicle Statistics -->
        <div class="col-12 col-lg-6 col-xl-6 col-xxl-5 order-lg-0 order-1 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Reservation Statistics</h5>
                </div>
                <div class="card-body row gap-md-0 gap-4">
                    <div class="col-md-5">
                        <h1 class="mb-0 text-nowrap">{{$reservationsTotal}}</h1>
                        <div id="approvedReservationPercentage" class="ms-n3"></div>
                    </div>
                    <div class="col-md-7 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex flex-column">
                                <small class="text-muted">Pending</small>
                                <span class="fw-semibold"> {{$reservationsPending}}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Approved</small>
                                <span class="fw-semibold"> {{$reservationsApproved}}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Canceled</small>
                                <span class="fw-semibold"> {{$reservationsCanceled}}</span>
                            </div>
                        </div>
                        <div class="progress-wrapper mb-4">
                            <div class="mb-3">
                                <small class="text-muted">Pending</small>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style="height:8px">
                                        <div class="progress-bar bg-primary" style="width: {{$reservationsPendingPercentage}}%" role="progressbar" aria-valuenow="{{$reservationsPendingPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{$reservationsPendingPercentage}}%</small>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted">Canceled</small>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style=" height:8px">
                                        <div class="progress-bar bg-primary" style="width: {{$reservationsCanceledPercentage}}%" role="progressbar" aria-valuenow="{{$reservationsCanceledPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{$reservationsCanceledPercentage}}%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <span class="d-block fw-semibold mb-2">Booking Sources</span>
                    <small class="text-muted d-block">Website</small>
                    <div class="d-flex align-items-center">
                        <div class="progress w-75 me-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{$sourceWebsitePercentage}}%" role="progressbar" aria-valuenow="{{$sourceWebsitePercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span>{{$sourceWebsitePercentage}}%</span>
                    </div>

                    <small class="text-muted d-block">Alonda App</small>
                    <div class="d-flex align-items-center">
                        <div class="progress w-75 me-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{$sourceAlondaPercentage}}%" role="progressbar" aria-valuenow="{{$sourceAlondaPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span>{{$sourceAlondaPercentage}}%</span>
                    </div>

                    <small class="text-muted d-block">Email</small>
                    <div class="d-flex align-items-center">
                        <div class="progress w-75 me-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{$sourceEmailPercentage}}%" role="progressbar" aria-valuenow="{{$sourceOtherPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span>{{$sourceOtherPercentage}}%</span>
                    </div>

                    <small class="text-muted d-block">Walk In</small>
                    <div class="d-flex align-items-center">
                        <div class="progress w-75 me-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{$sourceWalkInPercentage}}%" role="progressbar" aria-valuenow="{{$sourceWalkInPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span>{{$sourceWalkInPercentage}}%</span>
                    </div>

                    <small class="text-muted d-block">Other</small>
                    <div class="d-flex align-items-center">
                        <div class="progress w-75 me-2" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{$sourceEmailPercentage}}%" role="progressbar" aria-valuenow="{{$sourceEmailPercentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span>{{$sourceEmailPercentage}}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('vendors-js')
    {{-- add javascript resource links used in current page--}}
    <script src="{{asset('')}}cms/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/chartjs/chartjs.js"></script>
@endsection

@section('page-js')
    {{-- add javascript resource links and script tag for thes current page--}}
    <script src="{{asset('')}}cms/js/dashboards-ecommerce.js"></script>
{{--    <script src="{{asset('')}}cms/js/ui-cards-analytics.js"></script>--}}
    <script>
       const approvedReservation = document.querySelector("#approvedReservationPercentage");

       const approvedReservationChart = new ApexCharts(approvedReservation, {
           series: [{{$reservationsApprovedPercentage}}],
           labels: ["Approved"],
           chart: {height: 200, type: "radialBar"},
           colors: ["#62dd29"],
           plotOptions: {
               radialBar: {
                   offsetY: 0,
                   startAngle: -140,
                   endAngle: 140,
                   hollow: {size: "70%"},
                   track: {strokeWidth: "40%", background: "#a5a7ff"},
                   dataLabels: {
                       name: {offsetY: 60, color: "#fdae00", fontSize: "13px", fontFamily: "Public Sans"},
                       value: {offsetY: -10, color: "#c3c4ff", fontSize: "26px", fontWeight: "500", fontFamily: "Public Sans"}
                   }
               }
           },
           stroke: {lineCap: "round"},
           grid: {padding: {bottom: -20}},
           states: {hover: {filter: {type: "none"}}, active: {filter: {type: "none"}}}
       }).render();

       // newChart.render();

       const availableVehicles = document.querySelector("#availableVehiclesPercentage");

       const availableVehiclesChart = new ApexCharts(availableVehicles, {
           series: [{{$vehiclesAvailablePercentage}}],
           labels: ["Available"],
           chart: {height: 200, type: "radialBar"},
           colors: ["#62dd29"],
           plotOptions: {
               radialBar: {
                   offsetY: 0,
                   startAngle: -140,
                   endAngle: 140,
                   hollow: {size: "70%"},
                   track: {strokeWidth: "40%", background: "#a5a7ff"},
                   dataLabels: {
                       name: {offsetY: 60, color: "#fdae00", fontSize: "13px", fontFamily: "Public Sans"},
                       value: {offsetY: -10, color: "#c3c4ff", fontSize: "26px", fontWeight: "500", fontFamily: "Public Sans"}
                   }
               }
           },
           stroke: {lineCap: "round"},
           grid: {padding: {bottom: -20}},
           states: {hover: {filter: {type: "none"}}, active: {filter: {type: "none"}}}
       }).render();
    </script>

{{--    <script>--}}

{{--        var m =  new ApexCharts(document.querySelector("#activeUsersChart"), {--}}
{{--            chart: {height: 130, sparkline: {enabled: !0}, parentHeightOffset: 0, type: "radialBar"},--}}
{{--            colors: ["#fddc05"],--}}
{{--            series: [{{$totalActiveUsersPercentage}}],--}}
{{--            plotOptions: {--}}
{{--                radialBar: {--}}
{{--                    startAngle: -90,--}}
{{--                    endAngle: 90,--}}
{{--                    hollow: {size: "55%"},--}}
{{--                    track: {background: "#837c7c"},--}}
{{--                    dataLabels: {name: {show: !1}, value: {fontSize: "22px", color: "#cbcbe2", fontWeight: 500, offsetY: 0}}--}}
{{--                }--}}
{{--            },--}}
{{--            grid: {show: !1, padding: {left: -10, right: -10, top: -10}},--}}
{{--            stroke: {lineCap: "round"},--}}
{{--            labels: ["Progress"]--}}
{{--        }).render();--}}

{{--        // document.querySelector("#activeUsersChart")), c =--}}
{{--        let primaryColor = "#666ee81a", textMutedColor = "#7071a4";--}}
{{--        var thisWeekRegistration = document.querySelector("#thisWeekRegistration"), thisWeekRegistrationData = {--}}
{{--            chart: {height: 120, width: 200, parentHeightOffset: 0, type: "bar", toolbar: {show: !1}},--}}
{{--            plotOptions: {--}}
{{--                bar: {--}}
{{--                    barHeight: "75%",--}}
{{--                    columnWidth: "60%",--}}
{{--                    startingShape: "rounded",--}}
{{--                    endingShape: "rounded",--}}
{{--                    borderRadius: 9,--}}
{{--                    distributed: !0--}}
{{--                }--}}
{{--            },--}}
{{--            grid: {show: !1, padding: {top: -25, bottom: -12}},--}}
{{--            colors: [primaryColor, primaryColor, primaryColor, primaryColor, primaryColor, primaryColor, primaryColor],--}}
{{--            dataLabels: {enabled: !1},--}}
{{--            series: [{data: [40, 95, 60, 45, 90, 50, 75]}],--}}
{{--            legend: {show: !1},--}}
{{--            responsive: [{--}}
{{--                breakpoint: 1440,--}}
{{--                options: {plotOptions: {bar: {borderRadius: 9, columnWidth: "60%"}}}--}}
{{--            }, {breakpoint: 1300, options: {plotOptions: {bar: {borderRadius: 9, columnWidth: "60%"}}}}, {--}}
{{--                breakpoint: 1200,--}}
{{--                options: {plotOptions: {bar: {borderRadius: 8, columnWidth: "50%"}}}--}}
{{--            }, {breakpoint: 1040, options: {plotOptions: {bar: {borderRadius: 8, columnWidth: "50%"}}}}, {--}}
{{--                breakpoint: 991,--}}
{{--                options: {plotOptions: {bar: {borderRadius: 8, columnWidth: "50%"}}}--}}
{{--            }, {breakpoint: 420, options: {plotOptions: {bar: {borderRadius: 8, columnWidth: "50%"}}}}],--}}
{{--            xaxis: {--}}
{{--                categories: ["M", "T", "W", "T", "F", "S", "S"],--}}
{{--                axisBorder: {show: !1},--}}
{{--                axisTicks: {show: !1},--}}
{{--                labels: {style: {colors: textMutedColor, fontSize: "13px"}}--}}
{{--            },--}}
{{--            yaxis: {labels: {show: !1}}--}}
{{--        };--}}

{{--        let thisWeekRegistrationChart = new ApexCharts(thisWeekRegistration, thisWeekRegistrationData).render();--}}
{{--    </script>--}}

    <script>

    </script>
@endsection

