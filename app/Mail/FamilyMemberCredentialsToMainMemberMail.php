<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyMemberCredentialsToMainMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $mainMember,
        public User $familyMember,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.user_family_member_credentials_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.family-member-credentials-to-main-member',
        );
    }
}
