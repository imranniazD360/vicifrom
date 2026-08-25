<?php

declare(strict_types=1);

namespace Viciform\Laravel\Commands;

use Illuminate\Console\Command;
use Viciform\Laravel\EnvWriter;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'viciform:install
                            {--url= : Vicidial non_agent_api.php URL}
                            {--user= : Vicidial API username}
                            {--pass= : Vicidial API password}
                            {--list-id= : Default list ID}
                            {--source= : API source label}
                            {--phone-code= : Default phone country code}
                            {--duplicate-check= : Duplicate check mode}
                            {--force : Overwrite existing .env Viciform keys}';

    /**
     * @var string
     */
    protected $description = 'Auto-setup Viciform: publish config and write .env settings';

    /**
     * @return int
     */
    public function handle()
    {
        $this->info('Viciform install');
        $this->line('Publishing config and preparing .env ...');
        $this->line('');

        $this->publishConfig();

        $env = new EnvWriter($this->laravel->basePath('.env'));
        $example = new EnvWriter($this->laravel->basePath('.env.example'));

        if (!$env->exists() && !is_file($this->laravel->basePath('.env'))) {
            $this->warn('.env not found. Creating from defaults / .env.example if possible.');
            $examplePath = $this->laravel->basePath('.env.example');
            if (is_file($examplePath)) {
                copy($examplePath, $this->laravel->basePath('.env'));
            } else {
                file_put_contents($this->laravel->basePath('.env'), "");
            }
            $env = new EnvWriter($this->laravel->basePath('.env'));
        }

        $values = $this->resolveValues($env);

        if ($this->option('force')) {
            $env->setMany($values);
            $this->info('Updated Viciform keys in .env (--force).');
        } else {
            $added = $env->ensureKeys($values);
            if (count($added) > 0) {
                $this->info('Added to .env: ' . implode(', ', $added));
            } else {
                $this->comment('All Viciform .env keys already present (use --force to overwrite).');
            }

            // Always apply explicit CLI options even without --force
            $cliOverrides = $this->cliProvidedValues();
            if (count($cliOverrides) > 0) {
                $env->setMany($cliOverrides);
                $this->info('Applied CLI options to .env.');
            }
        }

        if ($example->exists() || is_file($this->laravel->basePath('.env.example'))) {
            $example->ensureKeys(EnvWriter::defaults());
            $this->comment('Synced keys into .env.example');
        }

        $this->line('');
        $this->table(
            ['Env key', 'Value'],
            $this->statusRows($env)
        );

        $this->line('');
        $this->info('Done. Edit .env if needed, then run:');
        $this->line('  php artisan viciform:configure   # interactive wizard');
        $this->line('  php artisan viciform:status      # show current config');
        $this->line('  php artisan viciform:test        # test Vicidial API');

        return 0;
    }

    /**
     * @return void
     */
    private function publishConfig()
    {
        $target = function_exists('config_path')
            ? config_path('viciform.php')
            : $this->laravel->basePath('config/viciform.php');

        $source = dirname(__DIR__) . '/config/viciform.php';

        if (!is_file($target) || $this->option('force')) {
            $dir = dirname($target);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($source, $target);
            $this->info('Published: config/viciform.php');
        } else {
            $this->comment('config/viciform.php already exists (use --force to replace).');
        }

        // Also register via vendor:publish for Laravel bookkeeping when available
        try {
            $this->callSilent('vendor:publish', [
                '--provider' => 'Viciform\\Laravel\\ViciformServiceProvider',
                '--tag' => 'viciform-config',
                '--force' => (bool) $this->option('force'),
            ]);
        } catch (\Throwable $e) {
            // Older Laravel or missing command — file copy above is enough
        }
    }

    /**
     * @param EnvWriter $env
     * @return array
     */
    private function resolveValues(EnvWriter $env)
    {
        $defaults = EnvWriter::defaults();
        $cli = $this->cliProvidedValues();

        $interactive = $this->input->isInteractive();

        if ($interactive && !$this->option('url') && !$this->option('user')) {
            if ($this->confirm('Configure Vicidial connection now?', true)) {
                $defaults['VICIFORM_BASE_URL'] = $this->ask(
                    'Vicidial API URL (non_agent_api.php)',
                    $env->get('VICIFORM_BASE_URL', $defaults['VICIFORM_BASE_URL'])
                );
                $defaults['VICIFORM_USER'] = $this->ask(
                    'API username',
                    $env->get('VICIFORM_USER', '')
                );
                $defaults['VICIFORM_PASS'] = $this->secret('API password') ?: $env->get('VICIFORM_PASS', '');
                $defaults['VICIFORM_LIST_ID'] = $this->ask(
                    'Default list ID',
                    $env->get('VICIFORM_LIST_ID', '999')
                );
                $defaults['VICIFORM_SOURCE'] = $this->ask(
                    'Source label (max 20 chars)',
                    $env->get('VICIFORM_SOURCE', 'webform')
                );
                $defaults['VICIFORM_PHONE_CODE'] = $this->ask(
                    'Phone country code',
                    $env->get('VICIFORM_PHONE_CODE', '1')
                );
                $defaults['VICIFORM_DUPLICATE_CHECK'] = $this->ask(
                    'Duplicate check (DUPCAMP/DUPLIST/YES/empty)',
                    $env->get('VICIFORM_DUPLICATE_CHECK', 'DUPCAMP')
                );

                return array_merge($defaults, $cli);
            }
        }

        return array_merge($defaults, $cli);
    }

    /**
     * @return array
     */
    private function cliProvidedValues()
    {
        $map = [
            'url' => 'VICIFORM_BASE_URL',
            'user' => 'VICIFORM_USER',
            'pass' => 'VICIFORM_PASS',
            'list-id' => 'VICIFORM_LIST_ID',
            'source' => 'VICIFORM_SOURCE',
            'phone-code' => 'VICIFORM_PHONE_CODE',
            'duplicate-check' => 'VICIFORM_DUPLICATE_CHECK',
        ];

        $out = [];
        foreach ($map as $option => $envKey) {
            $value = $this->option($option);
            if ($value === null || $value === false || $value === '') {
                continue;
            }
            $out[$envKey] = $value;
        }

        return $out;
    }

    /**
     * @param EnvWriter $env
     * @return array
     */
    private function statusRows(EnvWriter $env)
    {
        $rows = [];
        foreach (array_keys(EnvWriter::defaults()) as $key) {
            $value = (string) $env->get($key, '');
            if ($key === 'VICIFORM_PASS' && $value !== '') {
                $value = str_repeat('*', min(8, strlen($value)));
            }
            $rows[] = [$key, $value === '' ? '(empty)' : $value];
        }

        return $rows;
    }
}
