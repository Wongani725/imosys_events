<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('bookers', 'attire_color_id')) $drop[] = 'attire_color_id';
            if (Schema::hasColumn('bookers', 'attire_type')) $drop[] = 'attire_type';
            if (!empty($drop)) $table->dropColumn($drop);
        });
        
        if (!Schema::hasColumn('bookers', 'event_selection')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->enum('event_selection', ['governance', 'main'])->default('main')->after('event_id');
            });
        }
        if (!Schema::hasColumn('bookers', 'accommodation')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->boolean('accommodation')->default(false)->after('event_selection');
            });
        }
        if (!Schema::hasColumn('bookers', 'hotel_choice')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->enum('hotel_choice', ['nkopola', 'sun_n_sand'])->nullable()->after('accommodation');
            });
        }
        if (!Schema::hasColumn('bookers', 'spouse_included')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->boolean('spouse_included')->default(false)->after('hotel_choice');
            });
        }
        if (!Schema::hasColumn('bookers', 'extras')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->integer('extras')->default(0)->after('spouse_included');
            });
        }
        if (!Schema::hasColumn('bookers', 'room_type_id')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->unsignedBigInteger('room_type_id')->nullable()->after('extras');
            });
        }
        if (!Schema::hasColumn('bookers', 'room_allocated')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->string('room_allocated', 100)->nullable()->after('room_type_id');
            });
        }
        if (!Schema::hasColumn('bookers', 'invoice_status')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->enum('invoice_status', ['pending', 'sent', 'paid'])->default('pending')->after('proof_of_payment');
            });
        }
        if (!Schema::hasColumn('bookers', 'invoice_sent_at')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->timestamp('invoice_sent_at')->nullable()->after('invoice_status');
            });
        }
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropColumn([
                'event_selection', 'accommodation', 'hotel_choice', 'spouse_included', 
                'extras', 'room_type_id', 'room_allocated', 'invoice_status', 'invoice_sent_at'
            ]);
            $table->string('attire_color_id')->nullable();
            $table->string('attire_type')->nullable();
        });
    }
};
