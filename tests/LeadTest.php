<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Config;
use Viciform\Exceptions\ViciformException;
use Viciform\Lead;

final class LeadTest extends TestCase
{
    public function testFromArrayNormalizesPhoneAndAliases()
    {
        $lead = Lead::fromArray([
            'phone' => '(555) 123-4567',
            'fname' => 'Jane',
            'lname' => 'Doe',
            'zip' => '90210',
            'email_address' => 'jane@example.com',
            'external_id' => 'CRM-1',
        ]);

        $config = new Config([
            'base_url' => 'https://example.com/vicidial/non_agent_api.php',
            'user' => 'u',
            'pass' => 'p',
            'list_id' => '10001',
            'phone_code' => '1',
        ]);

        $params = $lead->toApiParams($config);

        $this->assertSame('add_lead', $params['function']);
        $this->assertSame('5551234567', $params['phone_number']);
        $this->assertSame('Jane', $params['first_name']);
        $this->assertSame('Doe', $params['last_name']);
        $this->assertSame('90210', $params['postal_code']);
        $this->assertSame('jane@example.com', $params['email']);
        $this->assertSame('CRM-1', $params['vendor_lead_code']);
        $this->assertSame('10001', $params['list_id']);
        $this->assertSame('1', $params['phone_code']);
    }

    public function testInvalidPhoneThrows()
    {
        $this->expectException(ViciformException::class);
        Lead::fromArray(['phone_number' => '123']);
    }

    public function testExtraCustomFieldsArePassedThrough()
    {
        $lead = Lead::fromArray([
            'phone_number' => '5551234567',
            'custom_score' => '99',
        ]);

        $config = new Config([
            'base_url' => 'https://example.com/api',
            'user' => 'u',
            'pass' => 'p',
        ]);

        $params = $lead->toApiParams($config);
        $this->assertSame('99', $params['custom_score']);
    }

    public function testDuplicateCheckFallsBackToConfig()
    {
        $lead = Lead::fromArray(['phone_number' => '5551234567']);

        $config = new Config([
            'base_url' => 'https://example.com/api',
            'user' => 'u',
            'pass' => 'p',
            'duplicate_check' => 'DUPCAMP',
        ]);

        $params = $lead->toApiParams($config);
        $this->assertSame('DUPCAMP', $params['duplicate_check']);
    }
}
