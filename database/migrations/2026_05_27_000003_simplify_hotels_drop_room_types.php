<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hotel', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel', 'quantity')) {
                $table->integer('quantity')->default(0)->after('name');
            }
            if (!Schema::hasColumn('hotel', 'available_count')) {
                $table->integer('available_count')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('hotel', 'booked_count')) {
                $table->integer('booked_count')->default(0)->after('available_count');
            }
        });

        if (Schema::hasTable('room_types')) {
            Schema::disableForeignKeyConstraints();

            if (Schema::hasColumn('bookers', 'room_type_id')) {
                Schema::table('bookers', function (Blueprint $table) {
                    if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                        $table->dropForeign(['room_type_id']);
                    }
                    $table->dropColumn('room_type_id');
                });
            }

            if (Schema::hasColumn('event_participants', 'room_type_id')) {
                Schema::table('event_participants', function (Blueprint $table) {
                    if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                        $table->dropForeign(['room_type_id']);
                    }
                    $table->dropColumn(['room_type_id', 'room_allocated']);
                });
            }

            Schema::dropIfExists('room_types');

            Schema::enableForeignKeyConstraints();
        }
    }

    public function down()
    {
        Schema::table('hotel', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'available_count', 'booked_count']);
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('name', 100);
            $table->integer('quantity')->default(0);
            $table->integer('available_count')->default(0);
            $table->integer('booked_count')->default(0);
            $table->timestamps();
            $table->foreign('hotel_id')->references('id')->on('hotel')->onDelete('cascade');
        });
    }
};
