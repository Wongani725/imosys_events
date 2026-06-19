<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  class="light-style layout-navbar-fixed layout-menu-fixed " dir="ltr" data-theme="theme-default" data-assets-path="{{asset('')}}cms/" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #myForm2 {
            background-image: url('{{ asset('images/IIM_Bannner_3000mm x 1500mm-02' . '.png')}}');
            {{--background-image: url('{{ asset('images/ICAM Card-02 (2)' . '.png')}}');--}}
            background-repeat: no-repeat;
            /*background-size: cover;*/
            background-position: center;
            background-size: contain;
            /* padding: 20px; */
            width: 550px; /* Adjust the width as needed */
            height: 700px; /* Adjust the height as needed */
            /*margin: 0 auto;*/
            display: flex;
            flex-direction: column; /* Ensure elements stack vertically */
            justify-content: center;
            align-items: center;
        }

        #myForm {
            background-image: url('{{ asset('images/IIM_Bannner_3000mm x 1500mm-02' . '.png')}}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            /* padding: 20px; */
            width: 600px; /* Adjust the width as needed */
            height: 800px; /* Adjust the height as needed */
            margin: 0 auto;
            display: flex;
            flex-direction: column; /* Ensure elements stack vertically */
            justify-content: center;
            align-items: center;
        }

    </style>
    <title>
        @hasSection('title')
            @yield('title')
            @elseicam
            {{config('app.name')}}
        @endif
    </title>

    <meta name="description" content="{{env('HEADER_DESCRIPTION')}}"/>

    <meta name="keywords" content="{{env('HEADER_KEYWORDS')}}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{env('HEADER_CANONICAL')}}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('/')}}/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('')}}cms/css/demo.css" />
    <style>
        :root {
            --iia-blue: #006198;
            --iia-bright-blue: #00ABE6;
            --iia-green: #97D700;
            --iia-dark-blue: #00305E;
            --iia-gray: #65656A;
            --iia-light-gray: #DBD9D6;
        }
        .bg-iia-blue { background-color: var(--iia-blue) !important; }
        .bg-iia-green { background-color: var(--iia-green) !important; }
        .bg-iia-dark-blue { background-color: var(--iia-dark-blue) !important; }
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
        .layout-menu .app-brand {
            background: linear-gradient(135deg, var(--iia-blue) 0%, var(--iia-dark-blue) 100%);
        }
    </style>

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
@yield('vendor-css')

<!-- Page CSS -->
    @yield('page-css')

    @include('layouts.vertical.head-js')

    @yield('head-js')

</head>

<body>
<?php
//    var_dump(session('user_session'));
?>
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar  ">
    <div class="layout-container">

    @include('layouts.vertical.menu')

    <!-- Layout container -->
        <div class="layout-page">

        @include("layouts.vertical.top-navbar")

        <!-- Content wrapper -->
            <div class="content-wrapper">

                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    @if($errors->any())
                        <div class="alert alert-danger">{{$errors->first()}}</div>
                    @endif

                    @yield('content')
                </div>
                <!-- / Content -->


                @include("layouts.vertical.footer")

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>



    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>


    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>

</div>
<!-- / Layout wrapper -->


@include("layouts.vertical.floating-button")


<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{asset('')}}cms/vendor/libs/jquery/jquery.js"></script>
<script src="{{asset('')}}cms/vendor/libs/popper/popper.js"></script>
<script src="{{asset('')}}cms/vendor/js/bootstrap.js"></script>
<script src="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="{{asset('')}}cms/vendor/libs/hammer/hammer.js"></script>
<script src="{{asset('')}}cms/vendor/libs/i18n/i18n.js"></script>
<script src="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.js"></script>

<script src="{{asset('')}}cms/vendor/js/menu.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
@yield('vendors-js')

<!-- Main JS -->
<script src="{{asset('')}}cms/js/main.js"></script>

<!-- Page JS -->
@yield('page-js')

</body>
</html>
