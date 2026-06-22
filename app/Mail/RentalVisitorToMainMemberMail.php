<?php

namespace App\Mail;

use App\Models\User;
use App\Models\VisitorEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentalVisitorToMainMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $mainMember,
        public User $rentalMember,
        public VisitorEntry $entry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.visitors_rental_email_subject', [
                'house' => $this->entry->houseUnit?->label() ?? '—',
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rental-visitor-to-main-member',
        );
    }
}
