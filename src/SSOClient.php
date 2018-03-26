<?php

namespace Qwildz\SSOClient;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class SSOClient
{
    private $client;

    function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setSid($accessToken)
    {
        $this->client->post(config('sso.url').'/session/set-sid', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],

            RequestOptions::JSON => [
                'sid' => session()->getId(),
            ]
        ]);
    }

    public function logout()
    {
        if($token = session('access_token')) {
            $this->client->delete(config('sso.url').'/session/'.$token);
        }
    }
}