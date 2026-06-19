<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $priceRow;
    public $extras;
    public $total;

    public function __construct($booking, $priceRow, $extras, $total)
    {
        $this->booking = $booking;
        $this->priceRow = $priceRow;
        $this->extras = $extras;
        $this->total = $total;
    }

    public function build()
    {
        $invoiceItems = $this->buildInvoiceItems();
        $invoice = \App\Models\BookingInvoice::where('booking_id', $this->booking->bookingID)->first();

        $pdf = Pdf::loadView('pdf.booking_invoice', [
            'booking' => $this->booking,
            'priceRow' => $this->priceRow,
            'invoice' => $invoice,
            'invoiceItems' => $invoiceItems,
            'total' => $this->total,
        ]);

        return $this->subject('Booking Invoice - ' . $this->booking->booking_reference ?? $this->booking->bookingID)
            ->markdown('emails.booking.invoice')
            ->with([
                'booking' => $this->booking,
                'invoiceItems' => $invoiceItems,
                'total' => $this->total,
            ])
            ->attachData($pdf->output(), 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
    }

    private function buildInvoiceItems()
    {
        $items = [];

        // 1. Base package (from event_prices)
        $items[] = [
            'description' => $this->priceRow->status, // or custom label
            'qty' => 1,
            'price' => $this->priceRow->price,
            'total' => $this->priceRow->price,
        ];

        // 2. Extras (spouse / additional persons)
        if ($this->extras > 0) {
            $items[] = [
                'description' => 'Additional Participants',
                'qty' => $this->extras,
                'price' => $this->priceRow->extra_person_price,
                'total' => $this->extras * $this->priceRow->extra_person_price,
            ];
        }

        return $items;
    }
}