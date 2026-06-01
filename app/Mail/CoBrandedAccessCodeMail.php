<?php

namespace App\Mail;

use App\Models\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoBrandedAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $collection,
        public string $email,
        public string $password,
        public string $accessUrl,
    ) {}

    public function build(): self
    {
        return $this
            ->to($this->email)
            ->subject('Votre accès à la collecte ' . $this->collection->company->name)
            ->view('emails.cobranded-access-code');
    }
}
