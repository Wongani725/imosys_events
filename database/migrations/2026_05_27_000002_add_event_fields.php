<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'background_image')) {
                $table->string('background_image', 2048)->nullable()->after('venue');
            }
            if (!Schema::hasColumn('events', 'certificate_background')) {
                $table->string('certificate_background', 2048)->nullable()->after('background_image');
            }
            if (!Schema::hasColumn('events', 'program_pdf')) {
                $table->string('program_pdf', 2048)->nullable()->after('certificate_background');
            }
            if (!Schema::hasColumn('events', 'total_sessions')) {
                $table->integer('total_sessions')->default(0)->after('program_pdf');
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['background_image', 'certificate_background', 'program_pdf', 'total_sessions']);
        });
    }
};
