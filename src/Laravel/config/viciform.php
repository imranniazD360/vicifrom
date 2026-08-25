<?php

declare(strict_types=1);

$verifySsl = env('VICIFORM_VERIFY_SSL', true);
if (is_string($verifySsl)) {
    $verifySsl = filter_var($verifySsl, FILTER_VALIDATE_BOOLEAN);
} else {
    $verifySsl = (bool) $verifySsl;
}

return [
    /*
    |--------------------------------------------------------------------------
    | Vicidial Non-Agent API URL
    |--------------------------------------------------------------------------
    |
    | Full URL to non_agent_api.php on your Vicidial server.
    |
    */
    'base_url' => env('VICIFORM_BASE_URL', 'https://your-server/vicidial/non_agent_api.php'),

    /*
    |--------------------------------------------------------------------------
    | API credentials
    |--------------------------------------------------------------------------
    |
    | Vicidial user must have API access, modify_leads = 1, user_level >= 8.
    |
    */
    'user' => env('VICIFORM_USER', ''),
    'pass' => env('VICIFORM_PASS', ''),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    'source' => env('VICIFORM_SOURCE', 'webform'),
    'list_id' => env('VICIFORM_LIST_ID', '999'),
    'phone_code' => env('VICIFORM_PHONE_CODE', '1'),
    'duplicate_check' => env('VICIFORM_DUPLICATE_CHECK'), // e.g. DUPCAMP, DUPLIST, YES
    'timeout' => (int) env('VICIFORM_TIMEOUT', 15),
    'verify_ssl' => $verifySsl,
];
