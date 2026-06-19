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
            if (Schema::hasColumn('bookers', 'hotel_choice')) $drop[] = 'hotel_choice';
            if (Schema::hasColumn('bookers', 'room_allocated')) $drop[] = 'room_allocated';
            if (!empty($drop)) $table->dropColumn($drop);
        });

        if (!Schema::hasColumn('bookers', 'hotel_id')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->unsignedBigInteger('hotel_id')->nullable()->after('accommodation');
                $table->foreign('hotel_id')->references('id')->on('hotel')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('bookers', 'admin_note')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->text('admin_note')->nullable()->after('booking_status');
            });
        }

        if (!Schema::hasColumn('bookers', 'cancellation_reason')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->string('cancellation_reason')->nullable()->after('admin_note');
            });
        }

        if (!Schema::hasColumn('bookers', 'restored_at')) {
            Schema::table('bookers', function (Blueprint $table) {
                $table->timestamp('restored_at')->nullable()->after('cancellation_reason');
            });
        }
    }

    public function down()
    {
        Schema::table('bookers', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn(['hotel_id', 'admin_note', 'cancellation_reason', 'restored_at']);
            $table->string('hotel_choice')->nullable();
            $table->string('room_allocated')->nullable();
        });
    }
};
