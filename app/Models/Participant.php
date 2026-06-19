<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "event_participants";
    protected $guarded = ['id', "created_at", "updated_at"];

    protected $fillable = [
        'event_id',
        'reference_code',
        'participant',
        'email_address',
        'phone_number',
        'company_name',
        'status',

        'hotel_id',
        'accommodation',
        'event_selection',
        'spouse_name',
        'extras_count',
        'booker_id',
        'is_walkin',
        'walkin_added_by',
        'meals',
        'extra_meals',
    ];

    protected $casts = [
        'accommodation' => 'boolean',
        'is_walkin' => 'boolean',
        'extras_count' => 'integer',
        'meals' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function booker()
    {
        return $this->belongsTo(Bookers::class, 'booker_id', 'bookingID');
    }

    public function mealCoupons()
    {
        return $this->hasMany(MealCoupon::class, 'participant_reference_code', 'reference_code');
    }

    public function attendanceRegistrations()
    {
        return $this->hasMany(AttendanceRegistration::class, 'reference_code', 'reference_code');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'reference_code', 'reference_code');
    }
}
