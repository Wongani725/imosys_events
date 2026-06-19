<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditDebtToMembers extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('credit', 15, 2)->default(0)->after('is_executive');
            $table->decimal('debt', 15, 2)->default(0)->after('credit');
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['credit', 'debt']);
        });
    }
}
