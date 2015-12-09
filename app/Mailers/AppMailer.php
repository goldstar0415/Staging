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
     * @var string Subject
     */
    protected $subject;

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
        $this->subject = 'Verify your account';
        $this->data = compact('user');

        $this->deliver();
    }

    public function notifyGeneratedUser(User $user, $password)
    {
        $this->to = $user->email;
        $this->view = 'emails.generated-user';
        $this->subject = 'Zoomtivity account';
        $this->data = compact('user', 'password');

        $this->deliver();
    }

    public function remindGeneratedUser(User $user, $password)
    {
        $this->to = $user->email;
        $this->view = 'emails.generated-reminder';
        $this->subject = 'Zoomtivity account';
        $this->data = compact('user', 'password');

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
            if ($this->subject) {
                $message->subject($this->subject);
            }
            $message->to($this->to);
        });
    }
}
