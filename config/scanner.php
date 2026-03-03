<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scanner Authentication Tokens
    |--------------------------------------------------------------------------
    |
    | Valid authentication tokens for RFID scanner hardware.
    | These tokens should be rotated every 90 days for security.
    |
    */
    'tokens' => [
        env('SCANNER_TOKEN_1'),
        env('SCANNER_TOKEN_2'),
        env('SCANNER_TOKEN_3'),
    ],
];
