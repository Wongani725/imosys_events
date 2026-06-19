<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            if (!Schema::hasColumn('bookers', 'attire_size_id')) {
                $table->unsignedBigInteger('attire_size_id')->nullable()->after('extras');
            }
        });

        Schema::table('event_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('event_participants', 'attire_size_id')) {
                $table->unsignedBigInteger('attire_size_id')->nullable()->after('extras_count');
            }
        });
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropColumn('attire_size_id');
        });
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn('attire_size_id');
        });
    }
};
