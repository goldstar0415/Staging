<?php

namespace App\Services\Social;

use App\Exceptions\TokenException;
use App\Http\Controllers\SocialContactsController;
use Config;

class GoogleClient
{
    /**
     * @var GoogleToken
     */
    protected $token;

    /**
     * @var int
     */
    protected $max_contacts = 99999;

    /**
     * @return GoogleToken
     * @throws TokenException
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * GoogleClient constructor.
     * @param GoogleToken $token
     */
    public function __construct(GoogleToken $token)
    {
        $this->token = $token;
    }

    public static function getContactsEngine()
    {
        Config::set('services.google.redirect', frontend_url('api/google-contacts'));
        $scopes = ['https://www.googleapis.com/auth/contacts.readonly'];
        $provider = \Socialite::with('google')->scopes($scopes);

        $engine = new \stdClass();
        $engine->provider = $provider;
        $engine->scopes = $scopes;

        return $engine;
    }

    public function getContacts()
    {
        $response = $this->getHttpClient()->get('https://www.google.com/m8/feeds/contacts/default/full', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken()->toString(),
            ],
            'query' => [
                'alt' => 'json',
                'max-results' => $this->max_contacts,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getHttpClient()
    {
        return new \GuzzleHttp\Client;
    }
}