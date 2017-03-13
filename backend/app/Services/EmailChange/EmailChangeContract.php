<?php

namespace App\Services\EmailChange;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

interface EmailChangeContract
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const RESET_LINK_SENT = 'emails.sent';

    /**
     * Constant representing a successfully reset password.
     *
     * @var string
     */
    const EMAIL_CHANGE = 'emails.change';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'emails.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'emails.token';

    /**
     * Send a password reset link to a user.
     *
     * @param  Authenticatable $user
     * @param $email
     * @param  \Closure|null $callback
     * @return string
     */
    public function sendChangeLink(Authenticatable $user, $email, Closure $callback = null);

    /**
     * Reset the password for the given token.
     *
     * @param \App\User $user
     * @param string $token
     * @param Closure|\Closure $callback
     * @return mixed
     * @internal param array $credentials
     */
    public function change($user, $token, Closure $callback);
}