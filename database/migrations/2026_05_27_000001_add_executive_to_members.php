<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('members', 'is_executive')) {
            Schema::table('members', function (Blueprint $table) {
                $table->boolean('is_executive')->default(false)->after('status');
            });
        }
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('is_executive');
        });
    }
};
