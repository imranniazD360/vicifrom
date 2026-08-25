<?php

declare(strict_types=1);

namespace Viciform\Laravel\Commands;

use Illuminate\Console\Command;
use Viciform\Laravel\EnvWriter;

class ConfigureCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'viciform:configure
                            {--url= : Vicidial API URL}
                            {--user= : API username}
                            {--pass= : API password}
                            {--list-id= : Default list ID}
                            {--source= : Source label}
                            {--phone-code= : Phone country code}
                            {--duplicate-check= : Duplicate check mode}
                            {--timeout= : Request timeout seconds}
                            {--verify-ssl= : true|false}';

    /**
     * @var string
     */
    protected $description = 'Configure Viciform settings in the .env file';

    /**
     * @return int
     */
    public function handle()
    {
        $envPath = $this->laravel->basePath('.env');
        if (!is_file($envPath)) {
            $this->error('.env file not found. Run: php artisan viciform:install');

            return 1;
        }

        $env = new EnvWriter($envPath);
        $values = [];

        if ($this->option('url') !== null) {
            $values['VICIFORM_BASE_URL'] = $this->option('url');
        }
        if ($this->option('user') !== null) {
            $values['VICIFORM_USER'] = $this->option('user');
        }
        if ($this->option('pass') !== null) {
            $values['VICIFORM_PASS'] = $this->option('pass');
        }
        if ($this->option('list-id') !== null) {
            $values['VICIFORM_LIST_ID'] = $this->option('list-id');
        }
        if ($this->option('source') !== null) {
            $values['VICIFORM_SOURCE'] = $this->option('source');
        }
        if ($this->option('phone-code') !== null) {
            $values['VICIFORM_PHONE_CODE'] = $this->option('phone-code');
        }
        if ($this->option('duplicate-check') !== null) {
            $values['VICIFORM_DUPLICATE_CHECK'] = $this->option('duplicate-check');
        }
        if ($this->option('timeout') !== null) {
            $values['VICIFORM_TIMEOUT'] = $this->option('timeout');
        }
        if ($this->option('verify-ssl') !== null) {
            $values['VICIFORM_VERIFY_SSL'] = $this->option('verify-ssl');
        }

        $interactive = $this->input->isInteractive() && count($values) === 0;

        if ($interactive) {
            $this->info('Viciform configuration wizard');
            $this->line('Values are written to .env');
            $this->line('');

            $values['VICIFORM_BASE_URL'] = $this->ask(
                'Vicidial API URL',
                $env->get('VICIFORM_BASE_URL', EnvWriter::defaults()['VICIFORM_BASE_URL'])
            );
            $values['VICIFORM_USER'] = $this->ask(
                'API username',
                $env->get('VICIFORM_USER', '')
            );
            $pass = $this->secret('API password (leave blank to keep current)');
            if ($pass !== null && $pass !== '') {
                $values['VICIFORM_PASS'] = $pass;
            }
            $values['VICIFORM_LIST_ID'] = $this->ask(
                'Default list ID',
                $env->get('VICIFORM_LIST_ID', '999')
            );
            $values['VICIFORM_SOURCE'] = $this->ask(
                'Source label',
                $env->get('VICIFORM_SOURCE', 'webform')
            );
            $values['VICIFORM_PHONE_CODE'] = $this->ask(
                'Phone country code',
                $env->get('VICIFORM_PHONE_CODE', '1')
            );
            $values['VICIFORM_DUPLICATE_CHECK'] = $this->ask(
                'Duplicate check (DUPCAMP/DUPLIST/YES or empty)',
                $env->get('VICIFORM_DUPLICATE_CHECK', 'DUPCAMP')
            );
            $values['VICIFORM_TIMEOUT'] = $this->ask(
                'Timeout (seconds)',
                $env->get('VICIFORM_TIMEOUT', '15')
            );
            $values['VICIFORM_VERIFY_SSL'] = $this->confirm(
                'Verify SSL certificates?',
                filter_var($env->get('VICIFORM_VERIFY_SSL', 'true'), FILTER_VALIDATE_BOOLEAN)
            ) ? 'true' : 'false';
        }

        if (count($values) === 0) {
            $this->warn('Nothing to update. Pass options or run interactively.');

            return 1;
        }

        $env->setMany($values);
        $env->ensureKeys();

        $example = new EnvWriter($this->laravel->basePath('.env.example'));
        if ($example->exists() || is_file($this->laravel->basePath('.env.example'))) {
            $example->ensureKeys(EnvWriter::defaults());
        }

        $this->info('Viciform .env updated.');
        $this->line('Run: php artisan config:clear');
        $this->line('Then: php artisan viciform:test');

        return 0;
    }
}
