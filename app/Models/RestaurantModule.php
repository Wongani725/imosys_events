<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantModule extends Model
{
protected $table = 'restaurant'; // Adjust the table name if needed

protected $fillable = [
'reference_code',
'status'
];
}
