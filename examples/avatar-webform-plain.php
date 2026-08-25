<?php

declare(strict_types=1);

/**
 * Plain PHP: parse Vicidial script GET params and build recording URL.
 */

require __DIR__ . '/../vendor/autoload.php';

use Viciform\Viciform;

// Simulate Vicidial query string
$_GET = [
    'lead_id' => '12345',
    'phone_number' => '5551234567',
    'first_name' => 'John',
    'last_name' => 'Smith',
    'campaign' => 'INSURANCE',
    'server_ip' => '10.0.0.5',
    'recording_filename' => '20240825-5551234567',
    'uniqueid' => '1724580000.123',
    'SIPexten' => 'SIP/1001',
    'dispo' => 'SALE',
    'closer' => 'CLO123',
    'agent_log_id' => '99887',
];

$payload = Viciform::webform();

echo 'Lead: ' . $payload->leadId() . PHP_EOL;
echo 'Phone: ' . $payload->phoneNumber() . PHP_EOL;
echo 'Recording: ' . $payload->recordingUrl() . PHP_EOL;
echo 'Closer code: ' . $payload->closerCode() . PHP_EOL;
print_r($payload->only([
    'campaign',
    'uniqueid',
    'SIPexten',
    'dispo',
    'agent_log_id',
]));
