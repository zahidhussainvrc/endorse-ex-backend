<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp)
    {

        // dd($otp);

        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
           $token =  $this->otp ;
        // return $this->view('view.name');
        return $this->subject('Your OTP Verification Code')
        ->view('emails.otp',compact('token'));
        // resources/views/emails/otp.blade.php
    }
}
