<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $table = 'event_registrations';

    protected $fillable = [
        'reference_code',
        'participant_name',
        'event_id',
        'registration_date_time',
        'conference_pack_redeemed',
    ];

    protected $casts = [
        'registration_date_time' => 'datetime',
        'conference_pack_redeemed' => 'boolean',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'reference_code', 'reference_code');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
