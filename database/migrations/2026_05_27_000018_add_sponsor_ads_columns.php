<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_ads', 'sponsor')) {
                $table->string('sponsor', 250)->nullable()->after('id');
            }
            if (!Schema::hasColumn('sponsor_ads', 'image')) {
                $table->string('image', 2048)->nullable()->after('event_id');
            }
            if (!Schema::hasColumn('sponsor_ads', 'start_date')) {
                $table->date('start_date')->nullable()->after('image');
            }
            if (!Schema::hasColumn('sponsor_ads', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            $table->dropColumn(['sponsor', 'image', 'start_date', 'end_date']);
        });
    }
};
