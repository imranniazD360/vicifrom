<?php

declare(strict_types=1);

namespace Viciform\Laravel\Commands;

use Illuminate\Console\Command;
use Viciform\Laravel\EnvWriter;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'viciform:status';

    /**
     * @var string
     */
    protected $description = 'Show Viciform configuration from .env / config';

    /**
     * @return int
     */
    public function handle()
    {
        $env = new EnvWriter($this->laravel->basePath('.env'));

        $this->info('Viciform status');
        $this->line('');

        $rows = [];
        foreach (array_keys(EnvWriter::defaults()) as $key) {
            $fromEnv = $env->get($key, null);
            $configKey = strtolower(str_replace('VICIFORM_', '', $key));
            // map env names to config keys
            $configMap = [
                'BASE_URL' => 'base_url',
                'USER' => 'user',
                'PASS' => 'pass',
                'SOURCE' => 'source',
                'LIST_ID' => 'list_id',
                'PHONE_CODE' => 'phone_code',
                'DUPLICATE_CHECK' => 'duplicate_check',
                'TIMEOUT' => 'timeout',
                'VERIFY_SSL' => 'verify_ssl',
            ];
            $short = str_replace('VICIFORM_', '', $key);
            $cfgKey = isset($configMap[$short]) ? $configMap[$short] : $configKey;
            $fromConfig = $this->laravel['config']->get('viciform.' . $cfgKey);

            $display = $fromEnv !== null ? $fromEnv : (string) $fromConfig;
            if ($key === 'VICIFORM_PASS') {
                $display = ($display !== '' && $display !== null)
                    ? str_repeat('*', min(8, strlen((string) $display)))
                    : '(empty)';
            } elseif ($display === '' || $display === null) {
                $display = '(empty)';
            } elseif (is_bool($fromConfig) && $fromEnv === null) {
                $display = $fromConfig ? 'true' : 'false';
            }

            $rows[] = [$key, $display];
        }

        $this->table(['Key', 'Value'], $rows);

        $user = $this->laravel['config']->get('viciform.user');
        $url = $this->laravel['config']->get('viciform.base_url');
        $pass = $this->laravel['config']->get('viciform.pass');

        $this->line('');
        if ($url && $user && $pass) {
            $this->info('Configuration looks complete.');
            $this->line('Test with: php artisan viciform:test');
        } else {
            $this->warn('Missing URL / user / pass. Run: php artisan viciform:configure');
        }

        return 0;
    }
}
