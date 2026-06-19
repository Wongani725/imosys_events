<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('sponsor_ads', 'name')) $drop[] = 'name';
            if (Schema::hasColumn('sponsor_ads', 'file_path')) $drop[] = 'file_path';
            if (!empty($drop)) $table->dropColumn($drop);
        });
    }

    public function down()
    {
        Schema::table('sponsor_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_ads', 'name')) {
                $table->string('name', 250)->nullable();
            }
        });
        Schema::table('sponsor_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsor_ads', 'file_path')) {
                $table->string('file_path', 250)->nullable();
            }
        });
    }
};
