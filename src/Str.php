<?php

declare(strict_types=1);

namespace Viciform;

/**
 * Tiny string helpers compatible with PHP 7.2+.
 */
final class Str
{
    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        if ($needle === '') {
            return true;
        }

        return strpos((string) $haystack, (string) $needle) === 0;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        if ($needle === '') {
            return true;
        }

        return strpos((string) $haystack, (string) $needle) !== false;
    }
}
