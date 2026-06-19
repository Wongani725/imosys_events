<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkBookingInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orgName;
    public $eventName;
    public $batchRef;
    public $bookings;
    public $totalAmount;

    public function __construct($orgName, $eventName, $batchRef, $bookings, $totalAmount)
    {
        $this->orgName = $orgName;
        $this->eventName = $eventName;
        $this->batchRef = $batchRef;
        $this->bookings = $bookings;
        $this->totalAmount = $totalAmount;
    }

    public function build()
    {
        $totalPeople = $this->bookings->count();
        $peopleWithAccommodation = $this->bookings->where('accommodation', true)->count();

        $pdf = Pdf::loadView('pdf.bulk_booking_invoice', [
            'bookings' => $this->bookings,
            'orgName' => $this->orgName,
            'eventNames' => $this->eventName,
            'totalAmount' => $this->totalAmount,
            'batchRef' => $this->batchRef,
            'totalPeople' => $totalPeople,
            'peopleWithAccommodation' => $peopleWithAccommodation,
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $this->subject('Invoice - ' . $this->orgName . ' - ' . $this->eventName)
            ->markdown('emails.bulk_booking_invoice')
            ->attachData($pdf->output(), 'invoice_' . $this->batchRef . '.pdf', [
                'mime' => 'application/pdf',
            ])
            ->with([
                'orgName' => $this->orgName,
                'eventName' => $this->eventName,
                'batchRef' => $this->batchRef,
                'bookings' => $this->bookings,
                'totalAmount' => $this->totalAmount,
            ]);
    }
}
