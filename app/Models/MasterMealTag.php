<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMealTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'member_id',
        'total_meals',
        'unique_code',

        'created_by',
    ];

    protected $casts = [
        'total_meals' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
