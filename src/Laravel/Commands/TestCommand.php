<?php

declare(strict_types=1);

namespace Viciform\Laravel\Commands;

use Illuminate\Console\Command;
use Viciform\Client;
use Viciform\Exceptions\ViciformException;

class TestCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'viciform:test
                            {--phone= : Optional phone to submit a real test lead}
                            {--first-name=Test : First name for test lead}
                            {--last-name=Lead : Last name for test lead}';

    /**
     * @var string
     */
    protected $description = 'Test Viciform connection to Vicidial (version call or test lead)';

    /**
     * @return int
     */
    public function handle()
    {
        try {
            /** @var Client $client */
            $client = $this->laravel->make(Client::class);
        } catch (ViciformException $e) {
            $this->error($e->getMessage());
            $this->line('Fix .env then run: php artisan config:clear');

            return 1;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Run: php artisan viciform:install');

            return 1;
        }

        $cfg = $client->config();
        $this->info('Connecting to: ' . $cfg->baseUrl());
        $this->line('User: ' . $cfg->user() . ' | List: ' . $cfg->listId() . ' | Source: ' . $cfg->source());
        $this->line('');

        $phone = $this->option('phone');

        try {
            if ($phone) {
                $this->comment('Submitting test lead for phone ' . $phone . ' ...');
                $response = $client->addLead([
                    'phone' => $phone,
                    'first_name' => $this->option('first-name'),
                    'last_name' => $this->option('last-name'),
                    'comments' => 'Viciform artisan test lead',
                    'vendor_lead_code' => 'VF-TEST-' . time(),
                ]);

                if ($response->isSuccess()) {
                    $this->info('SUCCESS lead_id=' . $response->leadId());
                    $this->line($response->raw());

                    return 0;
                }

                if ($response->isDuplicate()) {
                    $this->warn('DUPLICATE: ' . $response->message());

                    return 0;
                }

                $this->error('FAILED: ' . $response->message());

                return 1;
            }

            $this->comment('Calling Vicidial function=version ...');
            $response = $client->call('version');
            $this->line($response->raw());

            if ($response->isError() && stripos((string) $response->message(), 'error') !== false) {
                // Some Vicidial builds still return useful version text; treat hard ERROR as fail
                if (stripos((string) $response->raw(), 'ERROR') === 0) {
                    $this->error('API returned an error. Check user/pass and URL.');

                    return 1;
                }
            }

            $this->info('Connection OK.');
            $this->line('Optional: php artisan viciform:test --phone=5551234567');

            return 0;
        } catch (ViciformException $e) {
            $this->error($e->getMessage());

            return 1;
        }
    }
}
