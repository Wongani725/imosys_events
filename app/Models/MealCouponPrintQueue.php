<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealCouponPrintQueue extends Model
{
    use HasFactory;

    protected $table = 'meal_coupon_print_queues';

    protected $fillable = [
        'reference_code',
        'unique_code',
        'status',
        'total_meals',
        'meals_redeemed',

        'event_id',
        'day',
    ];
}
