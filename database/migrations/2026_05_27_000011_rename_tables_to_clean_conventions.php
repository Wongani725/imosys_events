<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $renames = [
            'i_user_otp'                     => 'member_otps',
            'To_form'                        => 'evaluation_submissions',
            'i_meal_coupons_print_queue'     => 'meal_coupon_print_queues',
            'i_user_event'                   => 'user_events',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }

    public function down()
    {
        $renames = [
            'member_otps'                    => 'i_user_otp',
            'evaluation_submissions'          => 'To_form',
            'meal_coupon_print_queues'        => 'i_meal_coupons_print_queue',
            'user_events'                     => 'i_user_event',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }
};
