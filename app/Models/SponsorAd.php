<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorAd extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'sponsor',
            'event_id',
            'file_path',
            'priority',
            'start_date',
            'end_date',
        ];
}
