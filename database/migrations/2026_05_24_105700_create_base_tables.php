<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Users table (Laravel default + custom)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('gender', ['female', 'male'])->nullable();
                $table->date('dob')->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
                $table->rememberToken();
                $table->unsignedBigInteger('current_team_id')->nullable();
                $table->string('profile_photo_path', 2048)->nullable();
                $table->string('firebase_token')->nullable();
                $table->string('unique_id', 32)->nullable();
                $table->unsignedBigInteger('total_web_logins')->default(0);
                $table->unsignedBigInteger('total_mobile_app_logins')->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // Members table
        if (!Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->string('member_id', 50)->unique()->nullable();
                $table->string('participant')->nullable();
                $table->string('email_address')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('company_name')->nullable();
                $table->string('status')->nullable();
                $table->text('address')->nullable();
                $table->string('password')->nullable();
                $table->boolean('password_set')->default(false);
                $table->string('otp')->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->string('datejoined')->nullable();
                $table->timestamp('last_active_at')->nullable();
                $table->string('reference_code')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Events table
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->enum('event_type', ['governance', 'main'])->nullable();
                $table->string('event_name', 250);
                $table->string('theme', 250)->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->string('event_venue', 250);
                $table->string('venue', 255)->nullable();
                $table->string('event_status', 250);
                $table->string('event_gps_coordinates', 250)->nullable();
                $table->timestamp('booking_start_time')->nullable();
                $table->timestamp('booking_end_time')->nullable();
                $table->timestamps();
            });
        }

        // Event sessions table
        if (!Schema::hasTable('event_sessions')) {
            Schema::create('event_sessions', function (Blueprint $table) {
                $table->id('session_id');
                $table->string('event_id', 250);
                $table->date('session_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('description', 250)->nullable();
                $table->timestamps();
            });
        }

        // Event programme table
        if (!Schema::hasTable('event_programme')) {
            Schema::create('event_programme', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->string('session_name', 250)->nullable();
                $table->text('session_description')->nullable();
                $table->date('session_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('presenter', 250)->nullable();
                $table->timestamps();
            });
        }

        // Event participants table
        if (!Schema::hasTable('event_participants')) {
            Schema::create('event_participants', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->string('reference_code', 255)->nullable();
                $table->string('registered', 250)->nullable();
                $table->string('phone_number', 250)->nullable();
                $table->string('email_address', 255)->nullable();
                $table->string('participant', 255)->nullable();
                $table->string('company_name', 255)->nullable();
                $table->unsignedBigInteger('hotel_id')->nullable();
                $table->unsignedBigInteger('room_type_id')->nullable();
                $table->string('room_allocated', 100)->nullable();
                $table->boolean('accommodation')->default(false);
                $table->enum('event_selection', ['governance', 'main'])->default('main');
                $table->integer('meals')->default(0);
                $table->string('extra_meals', 255)->nullable();
                $table->string('date_paid', 255)->nullable();
                $table->string('name', 255)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->string('event_name', 250)->nullable();
                $table->text('qr_code')->nullable();
                $table->string('qrcode_path', 2048)->nullable();
                $table->string('balance', 250)->nullable();
                $table->string('invoice_number', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->string('type', 250)->nullable();
                $table->timestamps();
            });
        }

        // Bookers table
        if (!Schema::hasTable('bookers')) {
            Schema::create('bookers', function (Blueprint $table) {
                $table->id('bookingID');
                $table->string('event_id', 250);
                $table->enum('event_selection', ['governance', 'main'])->default('main');
                $table->boolean('accommodation')->default(false);
                $table->enum('hotel_choice', ['nkopola', 'sun_n_sand'])->nullable();
                $table->boolean('spouse_included')->default(false);
                $table->integer('extras')->default(0);
                $table->unsignedBigInteger('room_type_id')->nullable();
                $table->string('room_allocated', 100)->nullable();
                $table->string('reference_code', 250)->nullable();
                $table->string('memberID', 250)->nullable();
                $table->string('name', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->string('datejoined', 250)->nullable();
                $table->string('email', 250)->nullable();
                $table->string('phone_number', 250)->nullable();
                $table->string('company', 250)->nullable();
                $table->string('position', 250)->nullable();
                $table->string('gender', 250)->nullable();
                $table->decimal('usd_fee', 15, 2)->nullable();
                $table->string('date_paid', 250)->nullable();
                $table->string('check_in', 250)->nullable();
                $table->string('check_out', 250)->nullable();
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->string('booking_status', 50)->default('Pending');
                $table->string('receipt_number', 250)->nullable();
                $table->string('date_verified', 250)->nullable();
                $table->decimal('amount_paid', 15, 2)->nullable();
                $table->string('mode_of_attendance', 250)->nullable();
                $table->decimal('balance', 15, 2)->nullable();
                $table->string('proof_of_payment', 250)->nullable();
                $table->enum('invoice_status', ['pending', 'sent', 'paid'])->default('pending');
                $table->timestamp('invoice_sent_at')->nullable();
                $table->string('member_type', 250)->nullable();
                $table->timestamps();
            });
        }

        // Hotel table
        if (!Schema::hasTable('hotel')) {
            Schema::create('hotel', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->enum('venue_type', ['governance', 'main', 'both'])->nullable();
                $table->string('name', 255);
                $table->string('gps_coordinates', 255)->nullable();
                $table->string('latitudes', 250)->nullable();
                $table->string('longitudes', 250)->nullable();
                $table->decimal('extra_price', 15, 2)->nullable();
                $table->timestamps();
            });
        }

        // Event prices table
        if (!Schema::hasTable('event_prices')) {
            Schema::create('event_prices', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250);
                $table->string('member_type', 50)->nullable();
                $table->boolean('accommodation')->default(false);
                $table->enum('hotel', ['nkopola', 'sun_n_sand'])->nullable();
                $table->boolean('spouse_included')->default(false);
                $table->enum('event_type', ['governance', 'main'])->nullable();
                $table->string('status', 100)->nullable();
                $table->decimal('price', 15, 2);
                $table->decimal('extra_person_price', 15, 2)->default(600000.00);
                $table->timestamps();
            });
        }

        // Attendance registration
        if (!Schema::hasTable('attendance_registration')) {
            Schema::create('attendance_registration', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250);
                $table->integer('session_id')->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }

        // Attendance registration logs
        if (!Schema::hasTable('attendance_registration_logs')) {
            Schema::create('attendance_registration_logs', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 255)->nullable();
                $table->integer('session_id')->nullable();
                $table->integer('event_id')->nullable();
                $table->timestamps();
            });
        }

        // Meal coupon
        if (!Schema::hasTable('meal_coupon')) {
            Schema::create('meal_coupon', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250)->nullable();
                $table->string('participant_reference_code', 255)->nullable();
                $table->string('unique_code', 255)->nullable();
                $table->integer('total_meals')->default(0);
                $table->string('qrcode_path', 2048)->nullable();
                $table->string('meals_redeemed', 250)->nullable();
                $table->string('day', 255)->nullable();
                $table->date('date')->nullable();
                $table->time('time')->nullable();
                $table->string('status', 255)->nullable();
                $table->timestamps();
            });
        }

        // Meal scans per day
        if (!Schema::hasTable('meal_scans_per_day')) {
            Schema::create('meal_scans_per_day', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250)->nullable();
                $table->string('participant_reference_code', 255);
                $table->string('unique_code', 255);
                $table->string('day', 255);
                $table->date('date')->nullable();
                $table->time('time')->nullable();
                $table->boolean('redeemed')->default(0);
                $table->string('hotel_name', 250)->nullable();
                $table->string('created_by', 250)->nullable();
                $table->timestamps();
            });
        }

        // Meal scans per day logs
        if (!Schema::hasTable('meal_scans_per_day_logs')) {
            Schema::create('meal_scans_per_day_logs', function (Blueprint $table) {
                $table->id();
                $table->string('participant_reference_code', 255)->nullable();
                $table->string('unique_code', 255)->nullable();
                $table->integer('day')->nullable();
                $table->date('date')->nullable();
                $table->time('time')->nullable();
                $table->integer('redeemed')->nullable();
                $table->string('hotel_name', 255)->nullable();
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Meal coupons print queue
        if (!Schema::hasTable('i_meal_coupons_print_queue')) {
            Schema::create('i_meal_coupons_print_queue', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('unique_code', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->integer('total_meals')->nullable();
                $table->string('meals_redeemed', 250)->nullable();
                $table->string('qrcode_path', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->string('day', 250)->nullable();
                $table->timestamps();
            });
        }

        // Participant event registrations
        if (!Schema::hasTable('i_participant_event_registrations')) {
            Schema::create('i_participant_event_registrations', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('participant_name', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamp('registration_date_time')->nullable();
                $table->boolean('conference_pack_redeemed')->default(false);
                $table->timestamps();
            });
        }

        // Participant event registrations logs
        if (!Schema::hasTable('i_participant_event_registrations_logs')) {
            Schema::create('i_participant_event_registrations_logs', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('participant_name', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamp('registration_date_time')->nullable();
                $table->boolean('conference_pack_redeemed')->default(false);
                $table->timestamps();
            });
        }

        // User OTP
        if (!Schema::hasTable('i_user_otp')) {
            Schema::create('i_user_otp', function (Blueprint $table) {
                $table->id();
                $table->string('email', 250)->nullable();
                $table->string('otp', 250)->nullable();
                $table->string('reference_code', 250)->nullable();
                $table->timestamps();
            });
        }

        // User event
        if (!Schema::hasTable('i_user_event')) {
            Schema::create('i_user_event', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250)->nullable();
                $table->string('user_id', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->timestamps();
            });
        }

        // Restaurant
        if (!Schema::hasTable('restaurant')) {
            Schema::create('restaurant', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250);
                $table->string('status', 250);
                $table->timestamps();
            });
        }

        // Restaurant module
        if (!Schema::hasTable('restaurant_module')) {
            Schema::create('restaurant_module', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250);
                $table->integer('total_meals');
                $table->string('meal_coupons', 500)->nullable();
                $table->timestamps();
            });
        }

        // Terms
        if (!Schema::hasTable('terms')) {
            Schema::create('terms', function (Blueprint $table) {
                $table->id();
                $table->text('terms')->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }

        // Sponsor ads
        if (!Schema::hasTable('sponsor_ads')) {
            Schema::create('sponsor_ads', function (Blueprint $table) {
                $table->id();
                $table->string('name', 250)->nullable();
                $table->string('file_path', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }

        // Booking forms
        if (!Schema::hasTable('booking_forms')) {
            Schema::create('booking_forms', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250)->nullable();
                $table->text('questions')->nullable();
                $table->timestamps();
            });
        }

        // Countries
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name', 250)->nullable();
                $table->string('code', 10)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // Participant evaluation (questions)
        if (!Schema::hasTable('participant_evaluation')) {
            Schema::create('participant_evaluation', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 250)->nullable();
                $table->text('questions')->nullable();
                $table->string('type', 250)->nullable();
                $table->timestamps();
            });
        }

        // To_form (evaluation submissions)
        if (!Schema::hasTable('To_form')) {
            Schema::create('To_form', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->text('answers')->nullable();
                $table->timestamps();
            });
        }

        // Authorization logs
        if (!Schema::hasTable('authorization_logs')) {
            Schema::create('authorization_logs', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->string('action', 250)->nullable();
                $table->string('performed_by', 250)->nullable();
                $table->timestamps();
            });
        }

        // Authorize event participants
        if (!Schema::hasTable('authorize_event_participants')) {
            Schema::create('authorize_event_participants', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->timestamps();
            });
        }

        // Authorize meal coupon
        if (!Schema::hasTable('authorize_meal_coupon')) {
            Schema::create('authorize_meal_coupon', function (Blueprint $table) {
                $table->id();
                $table->string('reference_code', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->string('status', 250)->nullable();
                $table->timestamps();
            });
        }

        // Attire types
        if (!Schema::hasTable('attire_types')) {
            Schema::create('attire_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }

        // Attire sizes
        if (!Schema::hasTable('attire_sizes')) {
            Schema::create('attire_sizes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }

        // Attire colors
        if (!Schema::hasTable('attire_colors')) {
            Schema::create('attire_colors', function (Blueprint $table) {
                $table->id();
                $table->string('name', 250)->nullable();
                $table->string('event_id', 250)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Not implementing full down migration for safety
    }
};
