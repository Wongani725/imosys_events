<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('invoice_number', 100)->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'sent', 'paid', 'overdue'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('booking_id')->references('bookingID')->on('bookers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_invoices');
    }
};
