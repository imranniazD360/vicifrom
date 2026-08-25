<?php

declare(strict_types=1);

namespace Viciform\Laravel;

/**
 * Read/write VICIFORM_* keys in Laravel .env / .env.example.
 */
class EnvWriter
{
    /** @var string */
    private $envPath;

    /**
     * @param string $envPath
     */
    public function __construct($envPath)
    {
        $this->envPath = $envPath;
    }

    /**
     * Default Viciform .env keys and values.
     *
     * @return array
     */
    public static function defaults()
    {
        return [
            'VICIFORM_BASE_URL' => 'https://your-server/vicidial/non_agent_api.php',
            'VICIFORM_USER' => '',
            'VICIFORM_PASS' => '',
            'VICIFORM_SOURCE' => 'webform',
            'VICIFORM_LIST_ID' => '999',
            'VICIFORM_PHONE_CODE' => '1',
            'VICIFORM_DUPLICATE_CHECK' => 'DUPCAMP',
            'VICIFORM_TIMEOUT' => '15',
            'VICIFORM_VERIFY_SSL' => 'true',
        ];
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return is_file($this->envPath) && is_writable($this->envPath);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return string|null
     */
    public function get($key, $default = null)
    {
        if (!is_file($this->envPath)) {
            return $default;
        }

        $content = file_get_contents($this->envPath);
        if ($content === false) {
            return $default;
        }

        if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return $this->unquote(trim($matches[1]));
        }

        return $default;
    }

    /**
     * Set one key. Creates file if missing when $create is true.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $create
     * @return bool
     */
    public function set($key, $value, $create = false)
    {
        if (!is_file($this->envPath)) {
            if (!$create) {
                return false;
            }
            file_put_contents($this->envPath, '');
        }

        $content = file_get_contents($this->envPath);
        if ($content === false) {
            return false;
        }

        $formatted = $this->formatValue($value);
        $line = $key . '=' . $formatted;

        if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $content)) {
            $content = preg_replace(
                '/^' . preg_quote($key, '/') . '=.*$/m',
                $line,
                $content,
                1
            );
        } else {
            $content = rtrim($content, "\r\n");
            if ($content !== '') {
                $content .= "\n";
            }
            if (strpos($content, '# Viciform') === false && strpos($content, 'VICIFORM_') === false) {
                $content .= "\n# Viciform (Vicidial webform API)\n";
            }
            $content .= $line . "\n";
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    /**
     * Ensure all default keys exist (does not overwrite existing values).
     *
     * @param array $values Optional overrides for missing keys only
     * @return array Keys that were added
     */
    public function ensureKeys(array $values = [])
    {
        $defaults = self::defaults();
        $merged = array_merge($defaults, $values);
        $added = [];

        foreach ($merged as $key => $value) {
            $current = $this->get($key, null);
            if ($current === null) {
                $this->set($key, $value, true);
                $added[] = $key;
            }
        }

        return $added;
    }

    /**
     * Write/overwrite multiple keys.
     *
     * @param array $values
     * @return void
     */
    public function setMany(array $values)
    {
        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->set($key, $value, true);
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function formatValue($value)
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private function unquote($value)
    {
        if ($value === '') {
            return '';
        }

        $first = $value[0];
        $last = substr($value, -1);
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
