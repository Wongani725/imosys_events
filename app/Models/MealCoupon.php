<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealCoupon extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "meal_coupon";
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $fillable = [
        'event_id',
        'participant_reference_code',
        'unique_code',
        'total_meals',

        'meals_redeemed',
        'day',
        'date',
        'time',
        'status',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_reference_code', 'reference_code');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function scopeNotRedeemed($query)
    {
        return $query->where('status', '!=', 'redeemed');
    }
}
