<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            if (!Schema::hasColumn('bookers', 'booking_reference')) {
                $table->string('booking_reference', 100)->nullable()->unique()->after('bookingID');
            }
        });

        // Migrate existing auto-increment IDs to reference format
        DB::statement("UPDATE bookers SET booking_reference = 'IIA-BK-' || bookingID WHERE booking_reference IS NULL");
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropColumn('booking_reference');
        });
    }
};
