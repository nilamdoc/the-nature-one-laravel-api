<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $invoicePdf)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Order confirmation - NatureOne');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-success');
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->view('emails.order-success')
            ->attachData($this->invoicePdf, 'invoice-' . $this->order->order_id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}

