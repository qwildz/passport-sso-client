<?php

namespace Qwildz\SSOClient;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class PassportSSOProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(config('sso.url') . '/oauth/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return config('sso.url') . '/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(config('sso.url') . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        $usermap = array();

        foreach (config('sso.usermap', []) as $key => $value) {
            if (is_numeric($key)) {
                $usermap[$value] = $user[$value];
            } else {
                $usermap[$key] = $user[$value];
            }
        }

        return (new User)->setRaw($user)->map($usermap);
    }
}