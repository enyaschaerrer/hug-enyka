<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company) {}

    public function build(): self
    {
        return $this
            ->to($this->company->email)
            ->subject('Confirmation d\'inscription')
            ->view('emails.company-registration');
    }
}
