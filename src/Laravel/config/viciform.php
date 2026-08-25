<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Viciform — Vicidial Non-Agent API
|--------------------------------------------------------------------------
|
| All values are read from your Laravel .env file.
| Setup quickly with:
|
|   php artisan viciform:install
|   php artisan viciform:configure
|   php artisan viciform:status
|   php artisan viciform:test
|
*/

$verifySsl = env('VICIFORM_VERIFY_SSL', true);
if (is_string($verifySsl)) {
    $verifySsl = filter_var($verifySsl, FILTER_VALIDATE_BOOLEAN);
} else {
    $verifySsl = (bool) $verifySsl;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Vicidial API URL
    |--------------------------------------------------------------------------
    |
    | Full URL to non_agent_api.php on your Vicidial server.
    | Env: VICIFORM_BASE_URL
    |
    */
    'base_url' => env('VICIFORM_BASE_URL', 'https://your-server/vicidial/non_agent_api.php'),

    /*
    |--------------------------------------------------------------------------
    | API credentials
    |--------------------------------------------------------------------------
    |
    | Vicidial user needs API access, modify_leads = 1, user_level >= 8.
    | Env: VICIFORM_USER / VICIFORM_PASS
    |
    */
    'user' => env('VICIFORM_USER', ''),
    'pass' => env('VICIFORM_PASS', ''),

    /*
    |--------------------------------------------------------------------------
    | Defaults for add_lead
    |--------------------------------------------------------------------------
    |
    | Env: VICIFORM_SOURCE, VICIFORM_LIST_ID, VICIFORM_PHONE_CODE,
    |      VICIFORM_DUPLICATE_CHECK, VICIFORM_TIMEOUT, VICIFORM_VERIFY_SSL
    |
    */
    'source' => env('VICIFORM_SOURCE', 'webform'),
    'list_id' => env('VICIFORM_LIST_ID', '999'),
    'phone_code' => env('VICIFORM_PHONE_CODE', '1'),
    'duplicate_check' => env('VICIFORM_DUPLICATE_CHECK', 'DUPCAMP'),
    'timeout' => (int) env('VICIFORM_TIMEOUT', 15),
    'verify_ssl' => $verifySsl,
];
