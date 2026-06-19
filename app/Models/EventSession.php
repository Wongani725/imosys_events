<?php

namespace App\Models;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class EventSession extends Model
{

    use HasFactory;
    public $timestamps =  false;
    protected $primaryKey = "session_id";
    protected $table = "event_sessions";
    protected $guarded = ['session_id', "event_id", "description"];

}
