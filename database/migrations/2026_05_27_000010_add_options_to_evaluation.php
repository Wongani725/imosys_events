<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('participant_evaluation', 'options')) {
            Schema::table('participant_evaluation', function (Blueprint $table) {
                $table->text('options')->nullable()->after('questions');
            });
        }
    }

    public function down()
    {
        Schema::table('participant_evaluation', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
