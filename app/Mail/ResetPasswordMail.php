<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $frontendUrl;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
        $this->frontendUrl = config('app.frontend_url');
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('reset-password')
                    ->with([
                        'token' => $this->token,
                        'email' => $this->email,
                        'frontendUrl' => $this->frontendUrl,
                    ]);
    }
}

