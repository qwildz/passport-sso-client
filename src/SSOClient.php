<?php

namespace Qwildz\SSOClient;

use GuzzleHttp\Client;

class SSOClient
{
    private $client;

    function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function logout()
    {
        if($token = session('access_token')) {
            $this->client->delete(config('sso.url').'/session/'.$token);
        }
    }
}