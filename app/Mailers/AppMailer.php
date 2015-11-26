<?php

namespace App\Mailers;

use App\User;
use Illuminate\Contracts\Mail\Mailer;

/**
 * Class AppMailer
 * @package App\Mailers
 */
class AppMailer
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var string Receiver address
     */
    protected $to;

    /**
     * @var string View template
     */
    protected $view;

    /**
     * @var array Data collection
     */
    protected $data;
    /**
     * AppMailer constructor.
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailVerification(User $user)
    {
        $this->to = $user->email;
        $this->view = 'emails.account-verifying';
        $this->data = compact('user');

        $this->deliver();
    }

    /**
     * Deliver email message
     */
    public function deliver()
    {
        $this->mailer->send($this->view, $this->data, function ($message) {
            /**
             * @var \Illuminate\Mail\Message $message
             */
            $message->to($this->to);
        });
    }
}
