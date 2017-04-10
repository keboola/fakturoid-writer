<?php

namespace Keboola\FakturoidWriter;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    private $client;

    public function __construct(array $parameters)
    {
        $this->client = new GuzzleClient([
            'base_uri' => 'https://app.fakturoid.cz/api/v2/accounts/' . $parameters['slug'] . '/',
            'auth' => [
                $parameters['email'],
                $parameters['#token']
            ],
            'headers' => [
                'User-Agent' => 'Keboola Fakturoid Writer/' . \GuzzleHttp\default_user_agent()
            ],
        ]);
    }

    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
}
