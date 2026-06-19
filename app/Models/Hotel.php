<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    protected $table = "hotel";
    protected $guarded = ['id', "created_at", "updated_at"];

    protected $fillable = [
        'event_id',
        'venue_type',
        'name',
        'quantity',
        'available_count',
        'booked_count',
        'gps_coordinates',
        'latitudes',
        'longitudes',
        'extra_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'available_count' => 'integer',
        'booked_count' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function bookers()
    {
        return $this->hasMany(Bookers::class, 'hotel_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'hotel_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_count', '>', 0);
    }
}
