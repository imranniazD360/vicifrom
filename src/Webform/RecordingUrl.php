<?php

declare(strict_types=1);

namespace Viciform\Webform;

/**
 * Builds Vicidial recording URLs.
 *
 * Default matches AvatarController:
 * http://{server_ip}/RECORDINGS/MP3/{recording_filename}-all.mp3
 */
final class RecordingUrl
{
    public const DEFAULT_PATH = '/RECORDINGS/MP3';
    public const DEFAULT_SUFFIX = '-all.mp3';

    /**
     * @param string|null $serverIp
     * @param string|null $recordingFilename
     * @param array{scheme?: string, path?: string, suffix?: string} $options
     * @return string|null
     */
    public static function build($serverIp, $recordingFilename, array $options = [])
    {
        $serverIp = $serverIp !== null ? trim((string) $serverIp) : '';
        $recordingFilename = $recordingFilename !== null ? trim((string) $recordingFilename) : '';

        if ($serverIp === '' || $recordingFilename === '') {
            return null;
        }

        // Strip scheme if a full host/URL was passed as server_ip
        $serverIp = preg_replace('#^https?://#i', '', $serverIp);
        $serverIp = rtrim((string) $serverIp, '/');

        // Avoid double suffix if filename already ends with -all.mp3 / .mp3
        $suffix = array_key_exists('suffix', $options)
            ? (string) $options['suffix']
            : self::DEFAULT_SUFFIX;

        if ($suffix !== '' && substr($recordingFilename, -strlen($suffix)) === $suffix) {
            $file = $recordingFilename;
        } elseif (preg_match('/\.mp3$/i', $recordingFilename)) {
            $file = $recordingFilename;
        } else {
            $file = $recordingFilename . $suffix;
        }

        $path = array_key_exists('path', $options)
            ? (string) $options['path']
            : self::DEFAULT_PATH;
        $path = '/' . trim($path, '/');

        $scheme = array_key_exists('scheme', $options)
            ? strtolower((string) $options['scheme'])
            : 'http';

        if ($scheme !== 'http' && $scheme !== 'https') {
            $scheme = 'http';
        }

        return $scheme . '://' . $serverIp . $path . '/' . ltrim($file, '/');
    }
}
