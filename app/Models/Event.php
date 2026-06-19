<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = "events";
    public $timestamps = false;
    protected $guarded = ["id", "created_at", "updated_at"];
    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'event_type',
        'event_name',
        'theme',
        'start_date',
        'end_date',
        'event_venue',
        'venue',
        'background_image',
        'certificate_background',
        'program_pdf',
        'total_sessions',
        'event_status',
        'event_gps_coordinates',
        'booking_start_time',
        'booking_end_time',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'booking_start_time' => 'datetime',
        'booking_end_time' => 'datetime',
        'total_sessions' => 'integer',
    ];

    public function sessions()
    {
        return $this->hasMany(EventSession::class, 'event_id', 'event_id');
    }

    public function prices()
    {
        return $this->hasMany(EventPrices::class, 'event_id', 'event_id');
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'event_id', 'event_id');
    }

    public function bookers()
    {
        return $this->hasMany(Bookers::class, 'event_id', 'event_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'event_id', 'event_id');
    }

    public function speakers()
    {
        return $this->hasMany(Speaker::class, 'event_id', 'event_id');
    }

    public function documents()
    {
        return $this->hasMany(EventDocument::class, 'event_id', 'event_id');
    }

    public function masterMealTags()
    {
        return $this->hasMany(MasterMealTag::class, 'event_id', 'event_id');
    }

    public function scopeActive($query)
    {
        return $query->where('event_status', 'active');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('start_date', 'desc');
    }
}
