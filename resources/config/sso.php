<?php

return [
    'url' => 'https://yourssourl',

    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect' => getenv('APP_URL') . '/callback',

    'usermap' => [
        'id',
        'name',
        'email',
    ],
];