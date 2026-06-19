<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPrices extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'status',
        'member_type',
        'accommodation',
        'hotel',
        'spouse_included',
        'event_type',
        'price',
        'extra_person_price',
    ];

    public function bookers()
    {
        return $this->hasMany(Bookers::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
