<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookers extends Model
{
    use HasFactory;
    protected $primaryKey = 'bookingID';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'bookingID',
        'booking_reference',
        'event_id',
        'event_selection',
        'accommodation',
        'hotel_id',
        'spouse_included',
        'extras',
        'reference_code',
        'memberID',
        'name',
        'status',
        'datejoined',
        'email',
        'phone_number',
        'company',
        'position',
        'gender',
        'usd_fee',
        'date_paid',
        'check_in',
        'check_out',
        'total_cost',
        'booking_status',
        'receipt_number',
        'date_verified',
        'amount_paid',
        'mode_of_attendance',
        'balance',
        'proof_of_payment',
        'invoice_status',
        'invoice_sent_at',
        'member_type',
        'attire_size_id',
        'admin_note',
        'cancellation_reason',
        'restored_at',
        'credit_applied',
        'debt_applied',
    ];

    protected $casts = [
        'accommodation' => 'boolean',
        'spouse_included' => 'boolean',
        'extras' => 'integer',
        'total_cost' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'debt_applied' => 'decimal:2',
        'restored_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'memberID', 'reference_code');
    }

    public function attireSize()
    {
        return $this->belongsTo(AttireSize::class, 'attire_size_id');
    }

    public function invoice()
    {
        return $this->hasOne(BookingInvoice::class, 'booking_id', 'bookingID');
    }

    public function participant()
    {
        return $this->hasOne(Participant::class, 'booker_id', 'bookingID');
    }

    public function statusInfo()
    {
        return $this->belongsTo(EventPrices::class, 'status');
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopePending($query)
    {
        return $query->where('booking_status', 'Pending Payment');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', 'Confirmed');
    }

    public function getStatusColorAttribute()
    {
        switch ($this->booking_status) {
            case 'Pending Payment':
                return 'warning';
            case 'Confirmed':
                return 'success';
            case 'Declined':
                return 'danger';
            case 'Cancelled':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    public function getInvoiceStatusColorAttribute()
    {
        switch ($this->invoice_status) {
            case 'pending':
                return 'warning';
            case 'sent':
                return 'info';
            case 'paid':
                return 'success';
            default:
                return 'secondary';
        }
    }
}
