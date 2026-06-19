<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditDebtAppliedToBookers extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->decimal('credit_applied', 15, 2)->default(0)->after('balance');
            $table->decimal('debt_applied', 15, 2)->default(0)->after('credit_applied');
        });
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropColumn(['credit_applied', 'debt_applied']);
        });
    }
}
