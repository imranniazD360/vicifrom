<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Response;

final class ResponseTest extends TestCase
{
    public function testParsesSuccessWithLeadId()
    {
        $response = new Response('SUCCESS|add_lead|lead inserted|12345');

        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isDuplicate());
        $this->assertFalse($response->isError());
        $this->assertSame('12345', $response->leadId());
    }

    public function testParsesSuccessLeadIdLabel()
    {
        $response = new Response('SUCCESS: add_lead lead_id: 998877');

        $this->assertTrue($response->isSuccess());
        $this->assertSame('998877', $response->leadId());
    }

    public function testParsesDuplicate()
    {
        $response = new Response('ERROR: add_lead DUPLICATE phone within list|5551234567');

        $this->assertTrue($response->isDuplicate());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isError());
    }

    public function testParsesError()
    {
        $response = new Response('ERROR: USER DOES NOT HAVE PERMISSION TO ADD LEADS');

        $this->assertTrue($response->isError());
        $this->assertFalse($response->isSuccess());
        $this->assertSame('ERROR: USER DOES NOT HAVE PERMISSION TO ADD LEADS', $response->message());
    }

    public function testToArray()
    {
        $response = new Response('SUCCESS|1|2|9001');
        $array = $response->toArray();

        $this->assertTrue($array['success']);
        $this->assertSame('9001', $array['lead_id']);
        $this->assertSame('SUCCESS|1|2|9001', $array['raw']);
    }
}
