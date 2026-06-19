<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttireSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'event_id',
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
