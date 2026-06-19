<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meal_scans_per_day', function (Blueprint $table) {
            $table->enum('meal_type', ['lunch', 'dinner'])->nullable()->after('hotel_name');
        });
    }

    public function down()
    {
        Schema::table('meal_scans_per_day', function (Blueprint $table) {
            $table->dropColumn('meal_type');
        });
    }
};
