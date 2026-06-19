<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            if (!Schema::hasColumn('bookers', 'room_number')) {
                $table->string('room_number', 100)->nullable()->after('hotel_id');
            }
        });

        Schema::table('event_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('event_participants', 'room_number')) {
                $table->string('room_number', 100)->nullable()->after('hotel_id');
            }
        });
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropColumn('room_number');
        });
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn('room_number');
        });
    }
};
