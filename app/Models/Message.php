<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = "aa_host_messages";
    public $timestamps = false;
    protected $guarded = ["id", "created_at", "updated_at"];

    public function recipients()
    {
        return $this->belongsToMany('App\Models\User', 'aa_host_message_recipient', 'message_id', 'user_id');
    }
}
