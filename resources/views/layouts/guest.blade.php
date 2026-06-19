<!DOCTYPE html>
<html lang="en" class="light-style  customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{asset('')}}cms//" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title')
            :@else
            {{config('app.name')}}
        @endif
    </title>

    <meta name="description" content="{{env('HEADER_DESCRIPTION')}}"/>

    <meta name="keywords" content="{{env('HEADER_KEYWORDS')}}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{env('HEADER_CANONICAL')}}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('MEI_LOGO.png')}}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('')}}cms//css/demo.css" />
    <style>
        :root {
            --iia-blue: #006198;
            --iia-bright-blue: #00ABE6;
            --iia-green: #97D700;
            --iia-dark-blue: #00305E;
        }
        .text-iia-blue { color: var(--iia-blue) !important; }
        .text-iia-green { color: var(--iia-green) !important; }
        .btn-iia-blue {
            background-color: var(--iia-blue);
            border-color: var(--iia-blue);
            color: white;
        }
        .btn-iia-blue:hover {
            background-color: var(--iia-dark-blue);
            border-color: var(--iia-dark-blue);
            color: white;
        }
        .btn-iia-green {
            background-color: var(--iia-green);
            border-color: var(--iia-green);
            color: white;
        }
        .btn-iia-green:hover {
            background-color: #7cb300;
            border-color: #7cb300;
            color: white;
        }
        .authentication-wrapper .card {
            border-top: 4px solid var(--iia-blue);
        }
    </style>

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/libs/formvalidation/dist/css/formValidation.min.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{asset('')}}cms//vendor/css/pages/page-auth.css">

@yield('style')
<!-- Helpers -->
    <script src="{{asset('')}}cms//vendor/js/helpers.js"></script>

    {{--    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->--}}
    <script src="{{asset('')}}cms//js/config.js"></script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async="async" src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
    <!-- Custom notification for demo -->
    <!-- beautify ignore:end -->

    @yield('head-script')
</head>

<body>

<!-- Content -->

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">

            <!-- /Form Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center">
                        <a href="" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <img src="{{asset('images/alogo2.png')}}" style="height: 200px !important; width: 200px !important;" alt="">
                            </span>
{{--                            <span class="app-brand-text demo text-body fw-bolder">{{ config('app.name', 'Alonda') }}</span>--}}
                        </a>
                    </div>
                    <!-- /Logo -->
                    @yield('content')
                </div>
            </div>
            <!-- /Form Card -->
        </div>
    </div>
</div>

<!-- / Content -->





<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{asset('')}}cms//vendor/libs/jquery/jquery.js"></script>
<script src="{{asset('')}}cms//vendor/libs/popper/popper.js"></script>
<script src="{{asset('')}}cms//vendor/js/bootstrap.js"></script>
<script src="{{asset('')}}cms//vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="{{asset('')}}cms//vendor/libs/hammer/hammer.js"></script>
<script src="{{asset('')}}cms//vendor/libs/i18n/i18n.js"></script>
<script src="{{asset('')}}cms//vendor/libs/typeahead-js/typeahead.js"></script>

<script src="{{asset('')}}cms//vendor/js/menu.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{asset('')}}cms//vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
<script src="{{asset('')}}cms//vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
<script src="{{asset('')}}cms//vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>

<!-- Main JS -->
<script src="{{asset('')}}cms/js/main.js"></script>

<!-- Page JS -->
@yield('script')

</body>

</html>
