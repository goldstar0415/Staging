<?php

namespace App\Services\Social;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Factory as Validator;

class GoogleContacts extends Collection
{
    /**
     * @var GoogleToken
     */
    private $token;

    public function __construct($items = [], GoogleToken $token)
    {
        $this->token = $token;
        parent::__construct($this->parse($items));
    }

    protected function parse(array $contacts)
    {
        $result = [];
        foreach ($contacts as $contact) {
            $first_name = '';
            $last_name = '';
            if (!empty($name = $contact['title']['$t'])) {
                if (strpos($name, ' ') !== false) {
                    list($first_name, $last_name) = explode(' ', $name);
                }
            }
            $result[] = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => isset($contact['gd$email'][0]['address']) ? $contact['gd$email'][0]['address'] : null,
                'phone' => isset($contact['gd$phoneNumber'][0]['$t']) ? $contact['gd$phoneNumber'][0]['$t'] : null,
                'photo' => $this->parsePhoto($contact['link'])
            ];

        }

        return $result;
    }

    protected function parsePhoto(array $link)
    {
        if (count($link) > 3) {
            $response = $this->getHttpClient()->get(
                $link[0]['href'],
                ['query' => ['access_token' => $this->token->toString()]]
            );

            return 'data:image/jpeg;base64,' . base64_encode((string)$response->getBody());
        }

        return null;
    }

    public function getHttpClient()
    {
        return new \GuzzleHttp\Client;
    }
}