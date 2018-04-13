<?php

return [
    'url' => 'https://yourssourl',

    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect' => getenv('APP_URL') . '/callback',

    'logout_frame' => getenv('APP_URL') . '/logout',

    'usermap' => [
        'id',
        'name',
        'email',
    ],
];