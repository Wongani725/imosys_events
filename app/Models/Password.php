<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "member_otps";
    protected $guarded = ["id", "created_at", "updated_at"];
}
