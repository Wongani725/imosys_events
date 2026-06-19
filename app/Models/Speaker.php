<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'title',
        'photo',
        'bio',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function ratings()
    {
        return $this->hasMany(SpeakerRating::class, 'speaker_id');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
}
