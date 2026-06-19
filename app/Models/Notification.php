<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'audience_type',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }

    public function sentCount()
    {
        return $this->recipients()->count();
    }

    public function readCount()
    {
        return $this->recipients()->whereNotNull('read_at')->count();
    }
}
