<?php

declare(strict_types=1);

namespace Viciform\Tests;

use PHPUnit\Framework\TestCase;
use Viciform\Viciform;
use Viciform\Webform\ParameterBridge;
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

    public function testParameterBridgeForDisplayMatchesParameterController()
    {
        $payload = ScriptPayload::fromArray([
            'lead_id' => '100',
            'phone_number' => '5559998888',
            'server_ip' => '10.1.1.1',
            'recording_filename' => 'rec-abc',
            'closer' => 'CLO456',
            'first_name' => 'Pat',
            'uniqueid' => '9.9',
            'SIPexten' => 'SIP/9',
            'dispo' => 'XFER',
        ]);

        $view = ParameterBridge::forDisplay($payload, [
            'dialer_map' => ['10.1.1.1' => 'D7'],
            'center_map' => ['clo' => 'West Center'],
            'currentDateTime' => '2024-08-25 12:00:00',
            'verifiers' => ['v1'],
        ]);

        $this->assertSame('100', $view['lead_id']);
        $this->assertSame('5559998888', $view['phone_number']);
        $this->assertSame('Pat', $view['first_name']);
        $this->assertSame('9.9', $view['uniqueid']);
        $this->assertSame('SIP/9', $view['SIPexten']);
        $this->assertSame('XFER', $view['dispo']);
        $this->assertSame('clo', $view['closer_code']);
        $this->assertSame('D7', $view['dialerMatch']);
        $this->assertSame('West Center', $view['centerMatch']);
        $this->assertSame('D7', $view['dailer_no']);
        $this->assertSame(
            'http://10.1.1.1/RECORDINGS/MP3/rec-abc-all.mp3',
            $view['recording_link']
        );
        $this->assertSame($view['recording_link'], $view['recordingLink']);
        $this->assertSame(['v1'], $view['verifiers']);
        $this->assertSame('2024-08-25 12:00:00', $view['currentDateTime']);
    }

    public function testParameterBridgeResolvers()
    {
        $payload = ScriptPayload::fromArray([
            'server_ip' => '8.8.8.8',
            'closer' => 'ABC999',
        ]);

        $view = ParameterBridge::forDisplay($payload, [
            'dialer_resolver' => function ($ip) {
                return $ip === '8.8.8.8' ? 'DIAL-1' : null;
            },
            'center_resolver' => function ($code) {
                return $code === 'abc' ? 'Center A' : null;
            },
        ]);

        $this->assertSame('DIAL-1', $view['dialerMatch']);
        $this->assertSame('Center A', $view['centerMatch']);
    }

    public function testParameterBridgeForStore()
    {
        $payload = ScriptPayload::fromArray([
            'lead_id' => '5',
            'agent_name' => 'Agent',
            'server_ip' => '1.2.3.4',
            'recording_filename' => 'f1',
            'smoker' => 'N',
            'age' => '40',
            'campaign' => 'C1',
        ]);

        $data = ParameterBridge::forStore($payload);

        $this->assertSame('5', $data['lead_id']);
        $this->assertSame('Agent', $data['agent_name']);
        $this->assertSame('N', $data['smoker']);
        $this->assertSame(
            'http://1.2.3.4/RECORDINGS/MP3/f1-all.mp3',
            $data['recordingLink']
        );
        $this->assertSame($data['recordingLink'], $data['recording_link']);
    }

    public function testViciformDisplayAndStoreAttributesHelpers()
    {
        $input = [
            'lead_id' => '77',
            'server_ip' => '9.9.9.9',
            'recording_filename' => 'z',
            'closer' => 'XYZ1',
        ];

        $view = Viciform::display($input, [
            'dialer_map' => ['9.9.9.9' => 'D9'],
            'center_map' => ['xyz' => 'HQ'],
        ]);

        $this->assertSame('77', $view['lead_id']);
        $this->assertSame('D9', $view['dialerMatch']);
        $this->assertSame('HQ', $view['centerMatch']);

        $store = Viciform::storeAttributes($input);
        $this->assertSame('77', $store['lead_id']);
        $this->assertSame(
            'http://9.9.9.9/RECORDINGS/MP3/z-all.mp3',
            $store['recordingLink']
        );
    }
}
