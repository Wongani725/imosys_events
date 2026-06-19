<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantEventRegistration extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "i_participant_event_registrations";
    protected $guarded = ["id", "created_at", "updated_at"];

    protected $fillable = [
        'reference_code',
        'participant_name',
        'event_id',
        'registration_date_time',
        'conference_pack_redeemed',
    ];
}
