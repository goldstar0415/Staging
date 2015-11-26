<?php


namespace App\Services\EmailChange;


use Illuminate\Contracts\Auth\Authenticatable;

interface TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $email
     * @return string
     */
    public function create(Authenticatable $user, $email);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(Authenticatable $user, $token);

    /**
     * Delete a token record.
     *
     * @param  string  $token
     * @return void
     */
    public function delete($token);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();

    /**
     * Get associated user id
     *
     * @param string $token
     * @return int
     */
    public function getNewEmail($token);
}