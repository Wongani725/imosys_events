<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('members', 'member_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('member_id', 50)->unique()->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('members', 'password_set')) {
            Schema::table('members', function (Blueprint $table) {
                $table->boolean('password_set')->default(false)->after('password');
            });
        }
        if (!Schema::hasColumn('members', 'otp_expires_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            });
        }

        if (Schema::hasColumn('members', 'firebase_token')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('firebase_token');
            });
        }
        if (Schema::hasColumn('members', 'device_type')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('device_type');
            });
        }
        if (Schema::hasColumn('members', 'years_at_bar')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('years_at_bar');
            });
        }
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['member_id', 'password_set', 'otp_expires_at']);
            $table->string('firebase_token')->nullable();
            $table->string('device_type')->nullable();
            $table->integer('years_at_bar')->nullable();
        });
    }
};
