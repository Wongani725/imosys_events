<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantMealCoupon extends Model
{
protected $table = 'meal_coupon'; // Adjust the table name if needed

protected $fillable = [
'reference_code',
'status'
];
}
