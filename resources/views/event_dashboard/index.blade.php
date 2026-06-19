<style>
    .dropdowns {
        position: absolute;
        top: 120px; /* Adjust the top distance as needed */
        right: 25px; /* Adjust the right distance as needed */
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: #f9f9f9;
        min-width: 260px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        padding: 10px 0;
        list-style-type: none;
    }

    .dropdown-content a {
        display: block;
        padding: 10px;
        text-decoration: none;
        color: #333;
        transition: background-color 0.3s ease;
    }

    .dropdown-content a:hover {
        background-color: #ddd;
    }

    .dropdowns:hover .dropdown-content {
        display: block;
    }
</style>

@extends('layouts.app')

@section('content')
    <div class="dropdowns">
        <button class="dropbtn">Reports</button>
        <div class="dropdown-content">

            <a href="{{ route('report') }}">Number of Meal Coupons Redeemed</a>
            <a href="{{ route('hotel-meal-report') }}">Number of Meal Coupons Used per Hotel per Meal</a>
            <a href="{{ route('participant-meal-report') }}">Number of Meal Coupons Assigned per Participant</a>

        </div>
    </div>

    
@endsection
