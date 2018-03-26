<?php

namespace Qwildz\SSOClient\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class SSOController
{
    public function handleLogout(Request $request)
    {
        $logoutToken = $request->get('token');

        $token = new \stdClass();

        try {
            $token = JWT::decode($logoutToken, config('sso.client_secret'), array('HS256'));
        } catch (\Exception $e) {
             abort(400, 'Bad request');
        }

        $claims = (array)$token;

        if ((!$this->validateLogoutToken($claims))
            || (!array_key_exists('sid', $claims))
            || (!array_key_exists('events', $claims))
            || (!array_key_exists('http://schemas.openid.net/event/backchannel-logout', (array)$claims['events']))
            || (array_key_exists('nonce', $claims))) {
             abort(400, 'Bad request');
        }

        $request->session()->setId($claims['sid']);
        $request->session()->invalidate();
        $request->session()->regenerate(true);

        return 200;
    }

    private function validateLogoutToken($claims)
    {
        return (
            hash_equals(config('sso.url'), $claims['iss'])
            && hash_equals(config('sso.client_id'), $claims['aud'])
            && ((time() - 30) <= $claims['iat'] && $claims['iat'] <= (time() + 30))
        );
    }

}