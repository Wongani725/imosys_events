<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable

{
    use HasFactory, HasApiTokens, Notifiable;

    public $timestamps = false;

    protected $table = "members";

    protected $guarded = ['id', "created_at", "updated_at"];

    protected $fillable = [
        'member_id',
        'participant',
        'email_address',
        'phone_number',
        'company_name',
        'status',
        'is_executive',
        'credit',
        'debt',
        'address',
        'password',
        'password_set',
        'otp',
        'otp_expires_at',
        'datejoined',
        'last_active_at',
        'reference_code',
    ];

    protected $casts = [
        'is_executive' => 'boolean',
        'credit' => 'decimal:2',
        'debt' => 'decimal:2',
        'password_set' => 'boolean',
        'otp_expires_at' => 'datetime',
    ];

    public function bookers()
    {
        return $this->hasMany(Bookers::class, 'memberID', 'reference_code');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'reference_code', 'reference_code');
    }

    public function notifications()
    {
        return $this->hasMany(NotificationRecipient::class, 'member_id');
    }

    public function masterMealTags()
    {
        return $this->hasMany(MasterMealTag::class, 'member_id');
    }

    public function scopeExecutives($query)
    {
        return $query->where('is_executive', true);
    }

    public function scopeMembers($query)
    {
        return $query->where('status', 'Member');
    }
}
