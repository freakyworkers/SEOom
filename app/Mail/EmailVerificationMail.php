<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Site;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $site;
    public $email;
    public $verificationCode;
    public $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(Site $site, string $email, string $verificationCode, string $fromName = null)
    {
        $this->site = $site;
        $this->email = $email;
        $this->verificationCode = $verificationCode;
        $this->fromName = $fromName ?? $site->name;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->fromName . ' 회원가입 인증번호 안내')
                    ->view('emails.verification')
                    ->with([
                        'site' => $this->site,
                        'email' => $this->email,
                        'verificationCode' => $this->verificationCode,
                        'fromName' => $this->fromName,
                    ]);
    }
}

