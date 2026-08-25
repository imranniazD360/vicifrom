# Viciform

PHP Composer package for **Vicidial** call-center integration:

1. **Outbound** — send webform leads via Non-Agent API (`add_lead` / `update_lead`)
2. **Inbound** — parse Avatar-style campaign-script webform params (like CRM `AvatarController`) and build recording URLs

Works with **plain PHP** and **Laravel** (5.8+ / 6–12).

## Requirements

- PHP **7.2+**
- `ext-curl`, `ext-json`
- Vicidial API user with `modify_leads = 1` and user level ≥ 8 (for outbound API)

## Install

```bash
composer require viciform/viciform
```

Repository: [github.com/imranniazD360/vicifrom](https://github.com/imranniazD360/vicifrom)

---

## Inbound: Avatar-style Vicidial webform

Vicidial campaign scripts open your CRM URL with query params. Parse them in one call:

```php
use Viciform\Viciform;

$payload = Viciform::webform($request); // Laravel Request, array, or null → $_GET+$_POST

$leadId = $payload->leadId();
$phone  = $payload->phoneNumber();
$url    = $payload->recordingUrl();
// http://{server_ip}/RECORDINGS/MP3/{recording_filename}-all.mp3
```

### Laravel controller (AvatarController pattern)

```php
use Viciform\Laravel\ViciformFacade as Viciform;

public function create(Request $request)
{
    $payload = Viciform::webform($request);

    return view('display', array_merge($payload->forView(), [
        'recording_link' => $payload->recordingUrl(),
        'closer_code' => $payload->closerCode(),
    ]));
}
```

### ParameterController pattern (`display` / `store`)

One-call replacement for the long `ParameterController::display` / `store` input lists, including dialer + center matching:

```php
use Viciform\Laravel\ViciformFacade as Viciform;
use Viciform\Laravel\Dialer;
use Viciform\Laravel\Center;
use Viciform\Laravel\WebformLead;

public function display(Request $request)
{
    return view('display', Viciform::display($request, [
        'dialer_resolver' => fn ($ip) => Dialer::matchNo($ip),
        'center_resolver' => fn ($code) => Center::matchName($code),
    ]));
}

public function store(Request $request)
{
    $data = Viciform::storeAttributes($request);
    WebformLead::fromWebform($data);
}
```

### Laravel migrations (Parameter / Avatar tables)

```bash
php artisan viciform:install --migrations
# or
php artisan vendor:publish --tag=viciform-migrations

php artisan migrate
```

Creates:

| Table | Purpose |
|-------|---------|
| `viciform_dialers` | Dialer IP → dialer_no (CRM `dialerlist_tb`) |
| `viciform_centers` | Closer code → center name (CRM `centerlist_tb`) |
| `viciform_recordings` | Recording URLs |
| `viciform_webform_leads` | Full Vicidial script + xfer fields (CRM `avatar_temp_leads` / `avatar_leads`) |

Migrations also auto-load from the package on `php artisan migrate` (no publish required).

Models: `Viciform\Laravel\Dialer`, `Center`, `Recording`, `WebformLead`.

See `examples/parameter-webform-controller.php`, `examples/avatar-webform-controller.php`, and `examples/avatar-webform-plain.php`.

### Vicidial script URL example

Point the campaign web form / script iframe at your route:

```text
https://crm.example.com/leads/create?lead_id=--A--lead_id--B--&phone_number=--A--phone_number--B--&first_name=--A--first_name--B--&last_name=--A--last_name--B--&list_id=--A--list_id--B--&campaign=--A--campaign--B--&server_ip=--A--server_ip--B--&recording_filename=--A--recording_filename--B--&recording_id=--A--recording_id--B--&uniqueid=--A--uniqueid--B--&SIPexten=--A--SIPexten--B--&dispo=--A--dispo--B--&agent_log_id=--A--agent_log_id--B--&closer=--A--closer--B--&vendor_id=--A--vendor_id--B--&user=--A--user--B--&phone_login=--A--phone_login--B--
```

### Recording URL

```php
use Viciform\Webform\RecordingUrl;

RecordingUrl::build('10.0.0.5', '20240825-5551234567');
// http://10.0.0.5/RECORDINGS/MP3/20240825-5551234567-all.mp3

RecordingUrl::build('dialer.example.com', 'call1', [
    'scheme' => 'https',
    'path' => '/RECORDINGS/MP3',
    'suffix' => '-all.mp3',
]);
```

### Complete inbound field catalog

| Field | Description |
|-------|-------------|
| `lead_id` | Vicidial lead ID |
| `vendor_id` | Vendor / external lead code |
| `list_id` | List ID |
| `entry_list_id` | Entry list ID |
| `source_id` | Source ID |
| `rank` | Lead rank |
| `owner` / `ownern` | Lead owner |
| `called_count` | Call count |
| `entry_date` | Entry datetime |
| `gmt_offset_now` | GMT offset |
| `phone_code` | Country dial code |
| `phone_number` / `phone` | Primary phone |
| `title` | Name title |
| `first_name` | First name |
| `middle_initial` | Middle initial |
| `last_name` | Last name |
| `address1`–`address3` | Address lines |
| `city` / `state` / `province` | Location |
| `postal_code` | Postal code |
| `country_code` | Country |
| `gender` | Gender |
| `date_of_birth` | DOB |
| `alt_phone` | Alternate phone |
| `email` | Email |
| `security_phrase` | Security phrase |
| `comments` | Comments |
| `user` / `pass` / `orig_pass` | Agent credentials (script-passed) |
| `phone_login` / `original_phone_login` / `phone_pass` | Phone login |
| `fronter` | Fronter agent |
| `agent_id` / `agent_name` / `fullname` / `agent_email` | Agent identity |
| `user_group` | User group |
| `session_id` / `session_name` | Session |
| `agent_log_id` | Agent log ID |
| `campaign` | Campaign ID |
| `list_name` / `list_description` | List meta |
| `closer` / `group` / `group_a` / `channel_group` | Groups |
| `dispo` | Disposition |
| `INOUT` | In/out flag |
| `SQLdate` / `epoch` | Dialer timestamps |
| `uniqueid` | Asterisk uniqueid |
| `call_id` / `closecallid` / `xfercallid` | Call IDs |
| `dialed_number` / `dialed_label` | Dialed number |
| `parked_by` | Parked-by |
| `server_ip` / `customer_server_ip` | Dialer IPs |
| `customer_zap_channel` | Zap channel |
| `SIPexten` | SIP extension |
| `camp_script` / `in_script` / `in_script_two` | Scripts |
| `script_width` / `script_height` | Script UI size |
| `recording_filename` / `recording_id` / `recording_link` | Recordings |
| `user_custom_one`–`five` | Agent custom fields |
| `preset_number_a`–`e` / `preset_dtmf_a`–`b` | Presets |
| `did_id` / `did_extension` / `did_pattern` / `did_description` | DID |
| `did_custom_one`–`five` | DID customs |
| `email_row_id` | Email row |
| `LOGINvarONE`–`FIVE` | Login vars |
| `hide_relogin_fields` / `web_vars` | Script misc |
| `center` / `dailer_no` / `dialer_no` / `dialer_id` | CRM dialer/center |
| `dialername` / `centername` | Display names |
| `smoker` / `Smoker` / `age` / `AGE` | Xfer form extras |
| `verifier_name` / `closer_name` / `xferSubmission` | Xfer form extras |

Unknown keys are kept in `$payload->extras()` and included via `toArrayWithExtras()`.

Helpers: `toArray()`, `forView()`, `only([...])`, `closerCode()` (first 3 chars of `closer`, lowercased).

---

## Outbound: Non-Agent API (`add_lead`)

### Plain PHP

```php
use Viciform\Viciform;

Viciform::configure([
    'base_url' => 'https://your-server/vicidial/non_agent_api.php',
    'user'     => 'apiuser',
    'pass'     => 'apipass',
    'source'   => 'webform',
    'list_id'  => '10001',
    'phone_code' => '1',
    'duplicate_check' => 'DUPCAMP',
]);

$response = Viciform::addLead([
    'phone'      => '5551234567',
    'first_name' => 'John',
    'last_name'  => 'Smith',
    'email'      => 'john@example.com',
    'comments'   => 'From website form',
    'vendor_lead_code' => 'WEB-1001',
]);

if ($response->isSuccess()) {
    echo $response->leadId();
} elseif ($response->isDuplicate()) {
    echo 'Duplicate: ' . $response->message();
} else {
    echo 'Error: ' . $response->message();
}
```

### Laravel (auto setup)

```bash
composer require viciform/viciform

php artisan viciform:install
# or non-interactive:
php artisan viciform:install \
  --url="https://your-server/vicidial/non_agent_api.php" \
  --user="apiuser" \
  --pass="apipass" \
  --list-id="10001" \
  --force

php artisan viciform:configure   # wizard
php artisan viciform:status      # show config (password masked)
php artisan viciform:test        # version ping
php artisan viciform:test --phone=5551234567
php artisan config:clear
```

### `.env` keys

```env
VICIFORM_BASE_URL=https://your-server/vicidial/non_agent_api.php
VICIFORM_USER=apiuser
VICIFORM_PASS=apipass
VICIFORM_SOURCE=webform
VICIFORM_LIST_ID=10001
VICIFORM_PHONE_CODE=1
VICIFORM_DUPLICATE_CHECK=DUPCAMP
VICIFORM_TIMEOUT=15
VICIFORM_VERIFY_SSL=true
```

### Outbound field aliases

| Your form key | Vicidial field |
|---------------|----------------|
| `phone` / `phone_number` | `phone_number` |
| `fname` / `firstname` / `first_name` | `first_name` |
| `lname` / `lastname` / `last_name` | `last_name` |
| `address` / `address1` | `address1` |
| `zip` / `zipcode` / `postal_code` | `postal_code` |
| `email` / `email_address` | `email` |
| `notes` / `comment` / `comments` | `comments` |
| `external_id` / `vendor_id` / `vendor_lead_code` | `vendor_lead_code` |

```php
Viciform::updateLead(['lead_id' => '12345', 'first_name' => 'Updated']);
Viciform::call('version');
```

See `examples/laravel-controller.php` for outbound webform → `add_lead`.

---

## Publish to Packagist

1. Push this repo to GitHub (already: `imranniazD360/vicifrom`).
2. Tag a release: `git tag v1.0.0 && git push origin v1.0.0`
3. Submit at [packagist.org/packages/submit](https://packagist.org/packages/submit)
4. Enable the GitHub service hook so tags auto-update.

Then anyone can install:

```bash
composer require viciform/viciform
```

## Development

```bash
composer install
composer test
```

## License

MIT
