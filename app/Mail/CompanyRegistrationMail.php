<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Form $form) {}

    public function build(): self
    {
        return $this
            ->to($this->form->email)
            ->subject('Confirmation de votre demande de collecte')
            ->view('emails.company-registration');
    }
}
