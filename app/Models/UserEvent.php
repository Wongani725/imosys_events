<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    use HasFactory;

    protected $table = 'user_events';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
    ];
}
