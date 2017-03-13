<?php

namespace App\Services\EmailChange;


use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Mail\Mailer;
use UnexpectedValueException;

class EmailChangeBroker implements EmailChangeContract
{
    /**
     * The password token repository.
     *
     * @var TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * The view of the email change link.
     *
     * @var string
     */
    protected $emailView;

    /**
     * Create a new password broker instance.
     *
     * @param  TokenRepositoryInterface  $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  string  $emailView
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens,
                                Mailer $mailer,
                                $emailView)
    {
        $this->mailer = $mailer;
        $this->tokens = $tokens;
        $this->emailView = $emailView;
    }

    /**
     * Send a password reset link to a user.
     *
     * @param Authenticatable $user
     * @param string $email
     * @param Closure|\Closure|null $callback
     * @return string
     */
    public function sendChangeLink(Authenticatable $user, $email, Closure $callback = null)
    {
        if (is_null($user)) {
            return EmailChangeContract::INVALID_USER;
        }

        // Once we have the reset token, we are ready to send the message out to this
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        $token = $this->tokens->create($user, $email);

        $this->emailChangeLink($user, $token, $email, $callback);

        return EmailChangeContract::RESET_LINK_SENT;
    }

    /**
     * Send the password reset link via e-mail.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @param string $email
     * @param  \Closure|null $callback
     * @return int
     */
    public function emailChangeLink(Authenticatable $user, $token, $email, Closure $callback = null)
    {
        // We will use the reminder view that was given to the broker to display the
        // password reminder e-mail. We'll pass a "token" variable into the views
        // so that it may be displayed for an user to click for password reset.
        $view = $this->emailView;

        return $this->mailer->send($view, compact('token', 'user'), function ($m) use ($user, $token, $email, $callback) {
            $m->to($email);

            if (! is_null($callback)) {
                call_user_func($callback, $m, $user, $token);
            }
        });
    }

    /**
     * Reset the password for the given token.
     *
     * @param \App\User $user
     * @param  string $token
     * @param Closure|\Closure $callback
     * @return mixed
     */
    public function change($user, $token, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $email = $this->validateToken($user, $token);

        if ($email === EmailChangeContract::INVALID_TOKEN) {
            return EmailChangeContract::INVALID_TOKEN;
        }
        // Once we have called this callback, we will remove this token row from the
        // table and return the response from this callback so the user gets sent
        // to the destination given by the developers from the callback return.
        call_user_func($callback, $user, $email);

        $this->tokens->delete($token);

        return EmailChangeContract::EMAIL_CHANGE;
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param $user
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\CanResetPassword
     */
    protected function validateToken($user, $token)
    {
        if (! $this->tokens->exists($user, $token)) {
            return EmailChangeContract::INVALID_TOKEN;
        }

        return $this->getRepository()->getNewEmail($token);
    }

    /**
     * Get the password reset token repository implementation.
     *
     * @return TokenRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->tokens;
    }
}
