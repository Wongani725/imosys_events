<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('event_prices', 'member_type')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->string('member_type', 50)->nullable()->after('event_id');
            });
        }
        if (!Schema::hasColumn('event_prices', 'accommodation')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->boolean('accommodation')->default(false)->after('member_type');
            });
        }
        if (!Schema::hasColumn('event_prices', 'hotel')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->enum('hotel', ['nkopola', 'sun_n_sand'])->nullable()->after('accommodation');
            });
        }
        if (!Schema::hasColumn('event_prices', 'spouse_included')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->boolean('spouse_included')->default(false)->after('hotel');
            });
        }
        if (!Schema::hasColumn('event_prices', 'event_type')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->enum('event_type', ['governance', 'main'])->nullable()->after('spouse_included');
            });
        }
        if (!Schema::hasColumn('event_prices', 'extra_person_price')) {
            Schema::table('event_prices', function (Blueprint $table) {
                $table->decimal('extra_person_price', 15, 2)->default(600000.00)->after('price');
            });
        }
    }

    public function down()
    {
        Schema::table('event_prices', function (Blueprint $table) {
            $table->dropColumn([
                'member_type', 'accommodation', 'hotel', 'spouse_included', 'event_type', 'extra_person_price'
            ]);
        });
    }
};
