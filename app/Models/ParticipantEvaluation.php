<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantEvaluation extends Model
{
    protected $table = 'evaluation_submissions';

    protected $fillable = [
        'reference_code',
        'event_id',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
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
