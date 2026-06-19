<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (!Schema::hasColumn('speakers', 'question_id')) {
                $table->unsignedBigInteger('question_id')->nullable()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (Schema::hasColumn('speakers', 'question_id')) {
                $table->dropColumn('question_id');
            }
        });
    }
};
