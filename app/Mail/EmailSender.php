<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailSender extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $userId;
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.confirmWorkerRegist')->with("userId", $this->userId)->from('register@email.pt', 'Register')->cc("m0@mail.pt", "sdjasj")->subject("Confirmation of your register");
        /*
        PODE VIR A DAR JEITO NO FUTURO: 
        Mail::send('emails.auth.activate', array(
                  'link' => URL::route('account-activate', $code), 
                  'username' => $username), 
                  function($message) use ($user) {
            $message->to($user->email, $user->username)->subject('Activate your account');
                });
        */
    }
}
