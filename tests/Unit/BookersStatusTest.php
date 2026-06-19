<?php

namespace Tests\Unit;

use App\Models\Bookers;
use PHPUnit\Framework\TestCase;

class BookersStatusTest extends TestCase
{
    private function makeBooker(string $status): Bookers
    {
        $booker = new Bookers();
        $booker->booking_status = $status;
        return $booker;
    }

    public function test_pending_payment_returns_warning()
    {
        $booker = $this->makeBooker('Pending Payment');
        $this->assertEquals('warning', $booker->getStatusColorAttribute());
    }

    public function test_confirmed_returns_success()
    {
        $booker = $this->makeBooker('Confirmed');
        $this->assertEquals('success', $booker->getStatusColorAttribute());
    }

    public function test_declined_returns_danger()
    {
        $booker = $this->makeBooker('Declined');
        $this->assertEquals('danger', $booker->getStatusColorAttribute());
    }

    public function test_cancelled_returns_secondary()
    {
        $booker = $this->makeBooker('Cancelled');
        $this->assertEquals('secondary', $booker->getStatusColorAttribute());
    }

    public function test_unknown_status_returns_secondary()
    {
        $booker = $this->makeBooker('Refunded');
        $this->assertEquals('secondary', $booker->getStatusColorAttribute());
    }

    public function test_invoice_status_pending_returns_warning()
    {
        $booker = new Bookers();
        $booker->invoice_status = 'pending';
        $this->assertEquals('warning', $booker->getInvoiceStatusColorAttribute());
    }

    public function test_invoice_status_sent_returns_info()
    {
        $booker = new Bookers();
        $booker->invoice_status = 'sent';
        $this->assertEquals('info', $booker->getInvoiceStatusColorAttribute());
    }

    public function test_invoice_status_paid_returns_success()
    {
        $booker = new Bookers();
        $booker->invoice_status = 'paid';
        $this->assertEquals('success', $booker->getInvoiceStatusColorAttribute());
    }
}
