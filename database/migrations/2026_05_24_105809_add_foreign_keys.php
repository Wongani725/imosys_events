<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('set null');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on('hotel')->onDelete('set null');
            $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropForeign(['room_type_id']);
        });
    }
};
