<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropUnique(['booking_reference']);
        });
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->string('booking_reference', 100)->nullable()->unique()->change();
        });
    }
};
