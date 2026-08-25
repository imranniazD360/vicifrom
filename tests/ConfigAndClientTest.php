<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Client;
use Viciform\Config;
use Viciform\Exceptions\ViciformException;
use Viciform\Response;
use Viciform\Viciform;

final class ConfigAndClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Viciform::reset();
        parent::tearDown();
    }

    public function testConfigRequiresBaseUrl()
    {
        $this->expectException(ViciformException::class);
        new Config(['user' => 'u', 'pass' => 'p']);
    }

    public function testAssertReadyRequiresUserAndPass()
    {
        $config = new Config([
            'base_url' => 'https://example.com/api',
            'user' => '',
            'pass' => '',
        ]);

        $this->expectException(ViciformException::class);
        $config->assertReady();
    }

    public function testConfigTruncatesSourceTo20Chars()
    {
        $config = new Config([
            'base_url' => 'https://example.com/api',
            'user' => 'u',
            'pass' => 'p',
            'source' => str_repeat('a', 30),
        ]);

        $this->assertSame(20, strlen($config->source()));
    }

    public function testViciformStaticConfigureAndAddLeadUsesFakeClient()
    {
        $fake = new class([
            'base_url' => 'https://example.com/api',
            'user' => 'api',
            'pass' => 'secret',
        ]) extends Client {
            /** @var array */
            public $lastParams = [];

            protected function request(array $params)
            {
                $this->lastParams = $params;

                return new Response('SUCCESS|add_lead|42');
            }
        };

        $ref = new \ReflectionClass(Viciform::class);
        $prop = $ref->getProperty('client');
        $prop->setAccessible(true);
        $prop->setValue(null, $fake);

        $response = Viciform::addLead([
            'phone_number' => '5559876543',
            'first_name' => 'Alex',
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame('42', $response->leadId());
        $this->assertSame('add_lead', $fake->lastParams['function']);
        $this->assertSame('5559876543', $fake->lastParams['phone_number']);
        $this->assertSame('api', $fake->lastParams['user']);
        $this->assertSame('Alex', $fake->lastParams['first_name']);
    }

    public function testUnconfiguredViciformThrows()
    {
        $this->expectException(ViciformException::class);
        Viciform::addLead(['phone_number' => '5551234567']);
    }
}
