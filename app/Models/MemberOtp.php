<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberOtp extends Model
{
    use HasFactory;

    protected $table = 'member_otps';

    protected $fillable = [
        'email',
        'otp',
        'reference_code',
    ];
}
