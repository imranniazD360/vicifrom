<?php

declare(strict_types=1);

/**
 * Plain PHP example — send a webform lead to Vicidial.
 *
 * Usage:
 *   composer install
 *   php examples/plain-php.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Viciform\Viciform;

Viciform::configure([
    'base_url' => getenv('VICIFORM_BASE_URL') ?: 'https://your-server/vicidial/non_agent_api.php',
    'user' => getenv('VICIFORM_USER') ?: 'apiuser',
    'pass' => getenv('VICIFORM_PASS') ?: 'apipass',
    'source' => 'webform',
    'list_id' => getenv('VICIFORM_LIST_ID') ?: '10001',
    'phone_code' => '1',
    'duplicate_check' => 'DUPCAMP',
    'timeout' => 15,
]);

// Simulate incoming webform POST
$form = [
    'phone' => $_POST['phone'] ?? '5551234567',
    'first_name' => $_POST['first_name'] ?? 'John',
    'last_name' => $_POST['last_name'] ?? 'Smith',
    'email' => $_POST['email'] ?? 'john@example.com',
    'city' => $_POST['city'] ?? 'Miami',
    'comments' => $_POST['comments'] ?? 'Submitted from website',
    'vendor_lead_code' => $_POST['ref'] ?? 'WEB-' . time(),
];

try {
    $response = Viciform::addLead($form);

    if ($response->isSuccess()) {
        echo "Lead created: {$response->leadId()}\n";
        exit(0);
    }

    if ($response->isDuplicate()) {
        echo "Duplicate lead: {$response->message()}\n";
        exit(0);
    }

    echo "Failed: {$response->message()}\n";
    exit(1);
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
