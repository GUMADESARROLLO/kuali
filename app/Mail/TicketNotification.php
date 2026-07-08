<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public ?TicketComment $comment;
    public string $action; // 'created' | 'commented' | 'resolved'

    public function __construct(Ticket $ticket, string $action, ?TicketComment $comment = null)
    {
        $this->ticket = $ticket;
        $this->action = $action;
        $this->comment = $comment;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->action) {
            'created' => "[{$this->ticket->ticket_number}] Nuevo ticket: {$this->ticket->title}",
            'commented' => "[{$this->ticket->ticket_number}] Nueva respuesta: {$this->ticket->title}",
            'resolved' => "[{$this->ticket->ticket_number}] Ticket resuelto: {$this->ticket->title}",
            default => "[{$this->ticket->ticket_number}] Actualización: {$this->ticket->title}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-notification');
    }
}
