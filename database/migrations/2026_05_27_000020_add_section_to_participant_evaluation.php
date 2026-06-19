<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('participant_evaluation', function (Blueprint $table) {
            if (!Schema::hasColumn('participant_evaluation', 'section')) {
                $table->string('section', 250)->nullable()->after('event_id');
            }
        });
    }

    public function down()
    {
        Schema::table('participant_evaluation', function (Blueprint $table) {
            if (Schema::hasColumn('participant_evaluation', 'section')) {
                $table->dropColumn('section');
            }
        });
    }
};
