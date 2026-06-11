<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollectionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipient,
        public string $companyName,
        public string $eligibilityUrl,
    ) {}

    public function build(): self
    {
        return $this
            ->to($this->recipient)
            ->subject('C\'est le moment de retenter le don du sang !')
            ->view('emails.collection-reminder');
    }
}
