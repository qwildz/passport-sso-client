<?php

namespace Qwildz\SSOClient;

use Exception;
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
        try {
            $response = $this->client->post(config('sso.url') . '/session/set-sid', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],

                RequestOptions::JSON => [
                    'sid' => session()->getId(),
                ]
            ]);

            session()->put('sso_session_state', json_decode($response->getBody(), true)['state']);
        } catch (Exception $e) {}
    }

    public function logout()
    {
        if ($token = session('access_token')) {
            try {
                $this->client->delete(config('sso.url') . '/session/' . session()->getId(), [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                    ]
                ]);
            } catch (Exception $e) {}
        }
    }
}