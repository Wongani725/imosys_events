<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('hotel', 'venue_type')) {
            Schema::table('hotel', function (Blueprint $table) {
                $table->enum('venue_type', ['governance', 'main', 'both'])->nullable()->after('event_id');
            });
        }
    }

    public function down()
    {
        Schema::table('hotel', function (Blueprint $table) {
            $table->dropColumn('venue_type');
        });
    }
};
