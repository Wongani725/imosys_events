<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'amount',
        'status',
        'sent_at',
        'paid_at',
    ];

    protected $dates = [
        'sent_at',
        'paid_at',
    ];

    public function booking()
    {
        return $this->belongsTo(Bookers::class, 'booking_id', 'bookingID');
    }
}
