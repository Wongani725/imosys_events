<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'name_tag_padding_top')) {
                $table->integer('name_tag_padding_top')->default(283)->after('total_sessions');
            }
            if (!Schema::hasColumn('events', 'name_tag_qr_top')) {
                $table->integer('name_tag_qr_top')->default(120)->after('name_tag_padding_top');
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'name_tag_padding_top')) {
                $table->dropColumn('name_tag_padding_top');
            }
            if (Schema::hasColumn('events', 'name_tag_qr_top')) {
                $table->dropColumn('name_tag_qr_top');
            }
        });
    }
};
