<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Server URL
    |--------------------------------------------------------------------------
    |
    | URL dari SSO Server Divusi untuk validasi token dan redirect login.
    |
    */
    'server_url' => env('SSO_SERVER_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout dalam detik untuk HTTP request ke SSO Server.
    |
    */
    'timeout' => 30,
];
