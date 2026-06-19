<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('events', 'event_type')) {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('event_type', ['governance', 'main'])->nullable()->after('event_id');
            });
        }
        if (!Schema::hasColumn('events', 'venue')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('venue', 255)->nullable()->after('event_venue');
            });
        }
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'venue']);
        });
    }
};
