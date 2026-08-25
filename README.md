# Viciform

PHP Composer package that sends webform leads to **Vicidial** via the Non-Agent API (`add_lead`). Works with **plain PHP** and **Laravel** (9–12).

## Requirements

- PHP **7.2+** (7.2, 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4+)
- `ext-curl`
- Vicidial API user with `modify_leads = 1` and user level ≥ 8

## Install

```bash
composer require viciform/viciform
```

> Change the package name in `composer.json` to your Packagist vendor (e.g. `imran/viciform`) before publishing.

## Plain PHP

```php
use Viciform\Viciform;

Viciform::configure([
    'base_url' => 'https://your-server/vicidial/non_agent_api.php',
    'user'     => 'apiuser',
    'pass'     => 'apipass',
    'source'   => 'webform',
    'list_id'  => '10001',
    'phone_code' => '1',
    'duplicate_check' => 'DUPCAMP', // optional: DUPCAMP, DUPLIST, YES
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

Or use the client directly:

```php
use Viciform\Client;

$client = new Client([/* same config */]);
$response = $client->addLead([...]);
```

## Laravel

1. Install the package (auto-discovery registers the provider & facade).
2. Publish config (optional):

```bash
php artisan vendor:publish --tag=viciform-config
```

3. Set `.env`:

```env
VICIFORM_BASE_URL=https://your-server/vicidial/non_agent_api.php
VICIFORM_USER=apiuser
VICIFORM_PASS=apipass
VICIFORM_SOURCE=webform
VICIFORM_LIST_ID=10001
VICIFORM_PHONE_CODE=1
VICIFORM_DUPLICATE_CHECK=DUPCAMP
```

4. Use the facade or inject `Viciform\Client`:

```php
use Viciform\Laravel\ViciformFacade as Viciform;

$response = Viciform::addLead($request->only([
    'phone', 'first_name', 'last_name', 'email', 'comments',
]));
```

See `examples/laravel-controller.php`.

## Field aliases

Accepted input keys map to Vicidial fields:

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

Unknown keys are forwarded as extra API parameters (useful for custom fields).

## Other API calls

```php
Viciform::updateLead([
    'lead_id' => '12345',
    'first_name' => 'Updated',
]);

Viciform::call('version');
```

## Config options

| Key | Description |
|-----|-------------|
| `base_url` | Full URL to `non_agent_api.php` |
| `user` / `pass` | API credentials |
| `source` | Origin label (max 20 chars) |
| `list_id` | Default list |
| `phone_code` | Default country code |
| `duplicate_check` | e.g. `DUPCAMP`, `DUPLIST`, `YES` |
| `timeout` | cURL timeout seconds (default 15) |
| `verify_ssl` | Verify TLS certificates (default true) |

## Publish to Packagist

1. Push this repo to GitHub.
2. Update `"name"` in `composer.json` to `your-vendor/viciform`.
3. Tag a release: `git tag v1.0.0 && git push --tags`
4. Submit the repo at [packagist.org](https://packagist.org).
5. Enable the GitHub service hook so tags auto-update.

## Development

```bash
composer install
composer test
```

## License

MIT
