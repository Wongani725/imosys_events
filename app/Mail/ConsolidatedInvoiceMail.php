<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsolidatedInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bookings;
    public $priceRows;

    public function __construct($bookings, $priceRows)
    {
        $this->bookings = $bookings;
        $this->priceRows = $priceRows;
    }

    public function build()
    {
        $items = [];
        $grandTotal = 0;

        foreach ($this->bookings as $i => $booking) {
            $priceRow = $this->priceRows[$i] ?? null;
            $eventItems = [];

            $eventItems[] = [
                'description' => $priceRow->status ?? 'Registration',
                'qty' => 1,
                'price' => $priceRow->price ?? $booking->total_cost,
                'total' => $priceRow->price ?? $booking->total_cost,
                'event_name' => $booking->event->event_name ?? $booking->event_id,
            ];

            $extras = $booking->extras ?? 0;
            if ($extras > 0 && $priceRow) {
                $eventItems[] = [
                    'description' => 'Additional Participants',
                    'qty' => $extras,
                    'price' => $priceRow->extra_person_price,
                    'total' => $extras * $priceRow->extra_person_price,
                    'event_name' => '',
                ];
            }

            $eventTotal = collect($eventItems)->sum('total');
            $grandTotal += $eventTotal;

            $items[] = [
                'event' => $booking->event->event_name ?? $booking->event_id,
                'items' => $eventItems,
                'subtotal' => $eventTotal,
                'credit' => $booking->credit_applied ?? 0,
                'debt' => $booking->debt_applied ?? 0,
                'balance' => $booking->balance ?? $eventTotal,
            ];
        }

        $pdf = Pdf::loadView('pdf.consolidated_invoice', [
            'bookings' => $this->bookings,
            'items' => $items,
            'grandTotal' => $grandTotal,
        ]);

        $ref = $this->bookings[0]->booking_reference ?? $this->bookings[0]->bookingID;

        return $this->subject('Booking Invoice - ' . $ref)
            ->markdown('emails.booking.consolidated_invoice')
            ->with([
                'bookings' => $this->bookings,
                'items' => $items,
                'grandTotal' => $grandTotal,
            ])
            ->attachData($pdf->output(), 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}