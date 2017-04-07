<?php

namespace App\Services\Social;

use App\Exceptions\TokenException;

class GoogleToken implements Token, \Serializable
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var int
     */
    protected $exp = 0;

    /**
     * GoogleToken constructor.
     * @param string $token
     * @param array $scopes
     */
    public function __construct($token, array $scopes)
    {
        $this->token = $token;
        $this->scopes = $scopes;
        $this->getTokenExpire();
    }

    protected function getTokenExpire()
    {
        if (!$this->exp) {
            $this->exp = (int)$this->info()->exp;
        }

        return $this->exp;
    }

    public function isExpired()
    {
        return time() > $this->getTokenExpire();
    }

    public function info()
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
            'query' => [
                'access_token' => $this->token
            ]
        ]);

        return json_decode($response->getBody());
    }
    /**
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new \GuzzleHttp\Client;
    }

    /**
     * @return string
     * @throws TokenException
     */
    public function toString()
    {
        if ($this->isExpired()) {
            throw new TokenException('Token is expired', 1);
        }

        return $this->token;
    }

    public function serialize()
    {
        return "token=$this->token&exp=$this->exp&scopes[]=" . implode('&scopes[]=', $this->getScopes());
    }

    public function unserialize($serialized)
    {
        $data = [];
        parse_str($serialized, $data);
        $this->token = $data['token'];
        $this->exp = (int)$data['exp'];
        $this->scopes = $data['scopes'];
    }

    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
