<?php

declare(strict_types=1);

namespace Viciform\Webform;

/**
 * Parsed Vicidial campaign-script / webform query parameters.
 *
 * Mirrors the inbound AvatarController / ParameterController flow:
 * Vicidial opens your CRM URL with script vars → parse → show form / store.
 */
final class ScriptPayload
{
    /**
     * Complete Vicidial campaign-script field catalog with short descriptions.
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        // Lead identity
        'lead_id' => 'Vicidial lead ID',
        'vendor_id' => 'Vendor / external lead code',
        'list_id' => 'List ID the lead belongs to',
        'entry_list_id' => 'Entry list ID',
        'source_id' => 'Source ID',
        'rank' => 'Lead rank',
        'owner' => 'Lead owner',
        'ownern' => 'Alternate owner field (script typo variant)',
        'called_count' => 'Times the lead has been called',
        'entry_date' => 'Lead entry date/time',

        // Contact / address
        'gmt_offset_now' => 'Current GMT offset for the lead',
        'phone_code' => 'Country dial code',
        'phone_number' => 'Primary phone number',
        'phone' => 'Alternate phone field from script',
        'title' => 'Name title',
        'first_name' => 'First name',
        'middle_initial' => 'Middle initial',
        'last_name' => 'Last name',
        'address1' => 'Address line 1',
        'address2' => 'Address line 2',
        'address3' => 'Address line 3',
        'city' => 'City',
        'state' => 'State',
        'province' => 'Province',
        'postal_code' => 'Postal / ZIP code',
        'country_code' => 'Country code',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of birth',
        'alt_phone' => 'Alternate phone',
        'email' => 'Email address',
        'security_phrase' => 'Security phrase',
        'comments' => 'Lead comments',

        // Agent / login session
        'user' => 'Vicidial agent user',
        'pass' => 'Agent password (script-passed)',
        'orig_pass' => 'Original agent password',
        'phone_login' => 'Phone login',
        'original_phone_login' => 'Original phone login',
        'phone_pass' => 'Phone password',
        'fronter' => 'Fronter agent',
        'agent_id' => 'App / CRM agent ID',
        'agent_name' => 'Agent display name',
        'fullname' => 'Agent full name (script)',
        'agent_email' => 'Agent email',
        'user_group' => 'User group',
        'session_id' => 'Agent session ID',
        'session_name' => 'Agent session name',
        'agent_log_id' => 'Agent log ID',

        // Campaign / call
        'campaign' => 'Campaign ID',
        'list_name' => 'List name',
        'list_description' => 'List description',
        'closer' => 'Closer / inbound group',
        'group' => 'Group (often mapped to group_a)',
        'group_a' => 'Group A / closer group alias',
        'channel_group' => 'Channel group',
        'dispo' => 'Disposition / call status',
        'INOUT' => 'Inbound or outbound flag',
        'SQLdate' => 'SQL datetime from dialer',
        'epoch' => 'Unix epoch from dialer',
        'uniqueid' => 'Asterisk uniqueid',
        'call_id' => 'Call ID',
        'closecallid' => 'Close call ID',
        'xfercallid' => 'Transfer call ID',
        'dialed_number' => 'Number dialed',
        'dialed_label' => 'Dialed label',
        'parked_by' => 'Parked-by agent',

        // Telephony / SIP
        'server_ip' => 'Dialer server IP',
        'customer_server_ip' => 'Customer channel server IP',
        'customer_zap_channel' => 'Customer Zap/DAHDI channel',
        'SIPexten' => 'SIP extension',

        // Scripts
        'camp_script' => 'Campaign script name',
        'in_script' => 'Inbound script',
        'in_script_two' => 'Second inbound script',
        'script_width' => 'Script iframe width',
        'script_height' => 'Script iframe height',

        // Recordings
        'recording_filename' => 'Recording base filename',
        'recording_id' => 'Recording ID',
        'recording_link' => 'Full recording URL (if provided)',

        // Agent custom fields
        'user_custom_one' => 'User custom field 1',
        'user_custom_two' => 'User custom field 2',
        'user_custom_three' => 'User custom field 3',
        'user_custom_four' => 'User custom field 4',
        'user_custom_five' => 'User custom field 5',

        // Presets
        'preset_number_a' => 'Preset number A',
        'preset_number_b' => 'Preset number B',
        'preset_number_c' => 'Preset number C',
        'preset_number_d' => 'Preset number D',
        'preset_number_e' => 'Preset number E',
        'preset_dtmf_a' => 'Preset DTMF A',
        'preset_dtmf_b' => 'Preset DTMF B',

        // DID
        'did_id' => 'DID ID',
        'did_extension' => 'DID extension',
        'did_pattern' => 'DID pattern',
        'did_description' => 'DID description',
        'did_custom_one' => 'DID custom 1',
        'did_custom_two' => 'DID custom 2',
        'did_custom_three' => 'DID custom 3',
        'did_custom_four' => 'DID custom 4',
        'did_custom_five' => 'DID custom 5',

        // Login vars / misc script
        'email_row_id' => 'Email row ID',
        'LOGINvarONE' => 'Login variable 1',
        'LOGINvarTWO' => 'Login variable 2',
        'LOGINvarTHREE' => 'Login variable 3',
        'LOGINvarFOUR' => 'Login variable 4',
        'LOGINvarFIVE' => 'Login variable 5',
        'hide_relogin_fields' => 'Hide relogin fields flag',
        'web_vars' => 'Extra web variables blob',

        // CRM / xfer form extras (Avatar-style)
        'center' => 'Call center code / name',
        'dailer_no' => 'Dialer number (CRM spelling)',
        'dialer_no' => 'Dialer number',
        'dialer_id' => 'Dialer ID',
        'dialername' => 'Dialer display name',
        'centername' => 'Center display name',
        'smoker' => 'Smoker flag (xfer form)',
        'age' => 'Age (xfer form)',
        'AGE' => 'Age uppercase variant',
        'Smoker' => 'Smoker uppercase variant',
        'verifier_name' => 'Verifier / closer name',
        'closer_name' => 'Closer display name',
        'xferSubmission' => 'Transfer submission timestamp / flag',
    ];

    /** @var array<string, string|null> */
    private $data;

    /** @var array<string, mixed> */
    private $extras;

    /**
     * @param array<string, mixed> $input
     */
    private function __construct(array $input)
    {
        $data = [];
        $extras = [];

        foreach ($input as $key => $value) {
            $key = (string) $key;

            if ($value === null || $value === '') {
                $normalized = null;
            } elseif (is_scalar($value)) {
                $normalized = (string) $value;
            } else {
                $encoded = json_encode($value);
                $normalized = $encoded === false ? null : $encoded;
            }

            if (array_key_exists($key, self::FIELDS)) {
                $data[$key] = $normalized;
            } else {
                $extras[$key] = $value;
            }
        }

        // group → group_a when group_a empty (ParameterController behaviour)
        if (
            (!isset($data['group_a']) || $data['group_a'] === null || $data['group_a'] === '')
            && isset($data['group'])
            && $data['group'] !== null
            && $data['group'] !== ''
        ) {
            $data['group_a'] = $data['group'];
        }

        $this->data = $data;
        $this->extras = $extras;
    }

    /**
     * @param array<string, mixed> $input
     * @return self
     */
    public static function fromArray(array $input)
    {
        return new self($input);
    }

    /**
     * Accepts Laravel Request, Symfony Request, or any object with all() / query->all().
     *
     * @param mixed $request
     * @return self
     */
    public static function fromRequest($request)
    {
        if (is_array($request)) {
            return self::fromArray($request);
        }

        if (is_object($request)) {
            if (method_exists($request, 'all')) {
                /** @var array<string, mixed> $all */
                $all = $request->all();

                return self::fromArray($all);
            }

            if (isset($request->query) && is_object($request->query) && method_exists($request->query, 'all')) {
                /** @var array<string, mixed> $all */
                $all = $request->query->all();

                return self::fromArray($all);
            }
        }

        return self::fromArray([]);
    }

    /**
     * @return self
     */
    public static function fromGlobals()
    {
        return self::fromArray(array_merge($_GET, $_POST));
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data) && $this->data[$key] !== null) {
            return $this->data[$key];
        }

        if (array_key_exists($key, $this->extras)) {
            $value = $this->extras[$key];

            if ($value === null || $value === '') {
                return $default;
            }

            return is_scalar($value) ? (string) $value : $default;
        }

        return $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->data) && $this->data[$key] !== null && $this->data[$key] !== '') {
            return true;
        }

        return array_key_exists($key, $this->extras)
            && $this->extras[$key] !== null
            && $this->extras[$key] !== '';
    }

    /**
     * Known catalog fields only (nulls omitted unless $includeNulls).
     *
     * @param bool $includeNulls
     * @return array<string, string|null>
     */
    public function toArray($includeNulls = false)
    {
        if ($includeNulls) {
            $out = [];
            foreach (array_keys(self::FIELDS) as $key) {
                $out[$key] = array_key_exists($key, $this->data) ? $this->data[$key] : null;
            }

            return $out;
        }

        $out = [];
        foreach ($this->data as $key => $value) {
            if ($value !== null && $value !== '') {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * Catalog fields + unknown extras.
     *
     * @param bool $includeNulls
     * @return array<string, mixed>
     */
    public function toArrayWithExtras($includeNulls = false)
    {
        return array_merge($this->toArray($includeNulls), $this->extras);
    }

    /**
     * @param string[] $keys
     * @return array<string, string|null>
     */
    public function only(array $keys)
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->get($key);
        }

        return $out;
    }

    /**
     * Values suitable for Laravel view compact / blade.
     *
     * @return array<string, string|null>
     */
    public function forView()
    {
        return $this->toArray(true);
    }

    /**
     * @return array<string, mixed>
     */
    public function extras()
    {
        return $this->extras;
    }

    /**
     * Build Vicidial MP3 recording URL from server_ip + recording_filename.
     *
     * @param array{scheme?: string, suffix?: string, path?: string} $options
     * @return string|null
     */
    public function recordingUrl(array $options = [])
    {
        $existing = $this->get('recording_link');
        if ($existing !== null && $existing !== '') {
            return $existing;
        }

        return RecordingUrl::build(
            $this->get('server_ip'),
            $this->get('recording_filename'),
            $options
        );
    }

    /**
     * First 3 chars of closer, lowercased (CRM center-code mapping helper).
     *
     * @return string|null
     */
    public function closerCode()
    {
        $closer = $this->get('closer');
        if ($closer === null || $closer === '') {
            return null;
        }

        return strtolower(substr($closer, 0, 3));
    }

    // --- Convenience getters (call-center ops) ---

    /** @return string|null */
    public function leadId()
    {
        return $this->get('lead_id');
    }

    /** @return string|null */
    public function phoneNumber()
    {
        return $this->get('phone_number') ?: $this->get('phone');
    }

    /** @return string|null */
    public function campaign()
    {
        return $this->get('campaign');
    }

    /** @return string|null */
    public function listId()
    {
        return $this->get('list_id');
    }

    /** @return string|null */
    public function uniqueId()
    {
        return $this->get('uniqueid');
    }

    /** @return string|null */
    public function sipExten()
    {
        return $this->get('SIPexten');
    }

    /** @return string|null */
    public function dispo()
    {
        return $this->get('dispo');
    }

    /** @return string|null */
    public function serverIp()
    {
        return $this->get('server_ip');
    }

    /** @return string|null */
    public function recordingFilename()
    {
        return $this->get('recording_filename');
    }

    /** @return string|null */
    public function agentLogId()
    {
        return $this->get('agent_log_id');
    }

    /** @return string|null */
    public function firstName()
    {
        return $this->get('first_name');
    }

    /** @return string|null */
    public function lastName()
    {
        return $this->get('last_name');
    }

    /** @return string|null */
    public function email()
    {
        return $this->get('email');
    }

    /** @return string|null */
    public function closer()
    {
        return $this->get('closer');
    }

    /** @return string|null */
    public function agentName()
    {
        return $this->get('agent_name') ?: $this->get('fullname');
    }
}
