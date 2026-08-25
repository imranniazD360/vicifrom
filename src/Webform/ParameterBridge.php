<?php

declare(strict_types=1);

namespace Viciform\Webform;

/**
 * ParameterController-style view / store helpers.
 *
 * Replaces the long hand-written $request->input(...) lists in
 * ParameterController::create / display / store.
 */
final class ParameterBridge
{
    /**
     * Fields ParameterController::store assigns onto AvatarLead.
     *
     * @var string[]
     */
    public const STORE_FIELDS = [
        'agent_id',
        'agent_name',
        'owner',
        'smoker',
        'age',
        'verifier_name',
        'dailer_no',
        'center',
        'lead_id',
        'list_id',
        'recording_filename',
        'phone_number',
        'campaign',
        'closer',
        'group_a',
        'server_ip',
        'dispo',
        'recording_id',
        'entry_list_id',
        'user_group',
        'list_name',
        'list_description',
        'entry_date',
        'closer_name',
        'dialername',
        'centername',
        'xferSubmission',
    ];

    /**
     * Build the display / xfer form view data (ParameterController::display).
     *
     * Options:
     * - dialer_map: array<string, string> server_ip => dialer_no
     * - center_map: array<string, string> closer_code => centerName
     * - dialer_resolver: callable(string $serverIp): ?string
     * - center_resolver: callable(string $closerCode): ?string
     * - verifiers: mixed (e.g. Eloquent collection) passed through to the view
     * - agents: mixed
     * - currentDateTime: string|\DateTimeInterface
     * - extra: array merged last
     *
     * @param ScriptPayload $payload
     * @param array{
     *     dialer_map?: array<string, string|null>,
     *     center_map?: array<string, string|null>,
     *     dialer_resolver?: callable,
     *     center_resolver?: callable,
     *     verifiers?: mixed,
     *     agents?: mixed,
     *     currentDateTime?: mixed,
     *     extra?: array<string, mixed>
     * } $options
     * @return array<string, mixed>
     */
    public static function forDisplay(ScriptPayload $payload, array $options = [])
    {
        $data = $payload->forView();

        $recordingLink = $payload->recordingUrl();
        $closerCode = $payload->closerCode();
        $serverIp = $payload->serverIp();

        $dialerMatch = self::resolveDialer($serverIp, $options);
        $centerMatch = self::resolveCenter($closerCode, $options);

        $currentDateTime = array_key_exists('currentDateTime', $options)
            ? $options['currentDateTime']
            : date('Y-m-d H:i:s');

        $out = array_merge($data, [
            'currentDateTime' => $currentDateTime,
            'recording_link' => $recordingLink,
            'recordingLink' => $recordingLink,
            'closer_code' => $closerCode,
            'closercode' => $closerCode,
            'dialerMatch' => $dialerMatch,
            'centerMatch' => $centerMatch,
            'dailer_no' => $payload->get('dailer_no') ?: $dialerMatch,
            'dialer_no' => $payload->get('dialer_no') ?: $dialerMatch,
            'centername' => $payload->get('centername') ?: $centerMatch,
        ]);

        if (array_key_exists('verifiers', $options)) {
            $out['verifiers'] = $options['verifiers'];
        }

        if (array_key_exists('agents', $options)) {
            $out['agents'] = $options['agents'];
        }

        if (!empty($options['extra']) && is_array($options['extra'])) {
            $out = array_merge($out, $options['extra']);
        }

        return $out;
    }

    /**
     * Attributes for saving a lead (ParameterController::store).
     *
     * Includes recordingLink / recording_link built from server_ip + filename.
     *
     * @param ScriptPayload $payload
     * @return array<string, string|null>
     */
    public static function forStore(ScriptPayload $payload)
    {
        $data = $payload->only(self::STORE_FIELDS);
        $link = $payload->recordingUrl();

        $data['recording_link'] = $link;
        $data['recordingLink'] = $link;

        return $data;
    }

    /**
     * @param string|null $serverIp
     * @param array $options
     * @return string|null
     */
    private static function resolveDialer($serverIp, array $options)
    {
        if ($serverIp === null || $serverIp === '') {
            return null;
        }

        if (isset($options['dialer_resolver']) && is_callable($options['dialer_resolver'])) {
            $resolved = call_user_func($options['dialer_resolver'], $serverIp);

            return $resolved === null || $resolved === '' ? null : (string) $resolved;
        }

        if (!empty($options['dialer_map']) && is_array($options['dialer_map'])) {
            if (array_key_exists($serverIp, $options['dialer_map'])) {
                $value = $options['dialer_map'][$serverIp];

                return $value === null || $value === '' ? null : (string) $value;
            }
        }

        return null;
    }

    /**
     * @param string|null $closerCode
     * @param array $options
     * @return string|null
     */
    private static function resolveCenter($closerCode, array $options)
    {
        if ($closerCode === null || $closerCode === '') {
            return null;
        }

        if (isset($options['center_resolver']) && is_callable($options['center_resolver'])) {
            $resolved = call_user_func($options['center_resolver'], $closerCode);

            return $resolved === null || $resolved === '' ? null : (string) $resolved;
        }

        if (!empty($options['center_map']) && is_array($options['center_map'])) {
            if (array_key_exists($closerCode, $options['center_map'])) {
                $value = $options['center_map'][$closerCode];

                return $value === null || $value === '' ? null : (string) $value;
            }
        }

        return null;
    }
}
