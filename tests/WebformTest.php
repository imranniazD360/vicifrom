<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Viciform;
use Viciform\Webform\RecordingUrl;
use Viciform\Webform\ScriptPayload;

final class WebformTest extends TestCase
{
    public function testRecordingUrlMatchesAvatarControllerPattern()
    {
        $url = RecordingUrl::build('10.0.0.5', '20240825-5551234567');

        $this->assertSame(
            'http://10.0.0.5/RECORDINGS/MP3/20240825-5551234567-all.mp3',
            $url
        );
    }

    public function testRecordingUrlHttpsAndCustomPath()
    {
        $url = RecordingUrl::build('dialer.example.com', 'call1', [
            'scheme' => 'https',
            'path' => '/REC',
            'suffix' => '.wav',
        ]);

        $this->assertSame('https://dialer.example.com/REC/call1.wav', $url);
    }

    public function testRecordingUrlReturnsNullWhenMissingParts()
    {
        $this->assertNull(RecordingUrl::build('', 'file'));
        $this->assertNull(RecordingUrl::build('10.0.0.1', ''));
    }

    public function testRecordingUrlDoesNotDoubleSuffix()
    {
        $url = RecordingUrl::build('1.2.3.4', 'file-all.mp3');
        $this->assertSame('http://1.2.3.4/RECORDINGS/MP3/file-all.mp3', $url);
    }

    public function testScriptPayloadParsesKnownAndExtraFields()
    {
        $payload = ScriptPayload::fromArray([
            'lead_id' => '99',
            'phone_number' => '5551112222',
            'campaign' => 'CAMP1',
            'uniqueid' => '1.2',
            'SIPexten' => 'SIP/2001',
            'dispo' => 'A',
            'server_ip' => '192.168.1.10',
            'recording_filename' => 'rec1',
            'agent_log_id' => '55',
            'group' => 'CLOSER1',
            'custom_app_field' => 'hello',
        ]);

        $this->assertSame('99', $payload->leadId());
        $this->assertSame('5551112222', $payload->phoneNumber());
        $this->assertSame('CAMP1', $payload->campaign());
        $this->assertSame('1.2', $payload->uniqueId());
        $this->assertSame('SIP/2001', $payload->sipExten());
        $this->assertSame('A', $payload->dispo());
        $this->assertSame('55', $payload->agentLogId());
        $this->assertSame('CLOSER1', $payload->get('group_a'));
        $this->assertSame('hello', $payload->extras()['custom_app_field']);
        $this->assertSame(
            'http://192.168.1.10/RECORDINGS/MP3/rec1-all.mp3',
            $payload->recordingUrl()
        );
    }

    public function testScriptPayloadPrefersExistingRecordingLink()
    {
        $payload = ScriptPayload::fromArray([
            'server_ip' => '1.1.1.1',
            'recording_filename' => 'x',
            'recording_link' => 'https://cdn.example/a.mp3',
        ]);

        $this->assertSame('https://cdn.example/a.mp3', $payload->recordingUrl());
    }

    public function testScriptPayloadFromRequestLikeObject()
    {
        $request = new class {
            public function all()
            {
                return [
                    'lead_id' => '7',
                    'first_name' => 'Ada',
                    'closer' => 'ABC999',
                ];
            }
        };

        $payload = ScriptPayload::fromRequest($request);

        $this->assertSame('7', $payload->leadId());
        $this->assertSame('Ada', $payload->firstName());
        $this->assertSame('abc', $payload->closerCode());
    }

    public function testOnlyAndForView()
    {
        $payload = ScriptPayload::fromArray([
            'lead_id' => '1',
            'phone_number' => '555',
        ]);

        $this->assertSame(
            ['lead_id' => '1', 'campaign' => null],
            $payload->only(['lead_id', 'campaign'])
        );

        $view = $payload->forView();
        $this->assertArrayHasKey('lead_id', $view);
        $this->assertSame('1', $view['lead_id']);
        $this->assertArrayHasKey('SIPexten', $view);
        $this->assertNull($view['SIPexten']);
    }

    public function testViciformWebformStaticHelper()
    {
        $payload = Viciform::webform([
            'lead_id' => '42',
            'email' => 'a@b.c',
        ]);

        $this->assertInstanceOf(ScriptPayload::class, $payload);
        $this->assertSame('42', $payload->leadId());
        $this->assertSame('a@b.c', $payload->email());
    }

    public function testFieldCatalogIsNonEmpty()
    {
        $this->assertGreaterThan(80, count(ScriptPayload::FIELDS));
        $this->assertArrayHasKey('lead_id', ScriptPayload::FIELDS);
        $this->assertArrayHasKey('SIPexten', ScriptPayload::FIELDS);
        $this->assertArrayHasKey('agent_log_id', ScriptPayload::FIELDS);
        $this->assertArrayHasKey('LOGINvarONE', ScriptPayload::FIELDS);
    }
}
