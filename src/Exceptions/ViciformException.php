<?php

declare(strict_types=1);

namespace Viciform\Exceptions;

use Exception;

class ViciformException extends Exception
{
    /**
     * @param string $key
     * @return self
     */
    public static function missingConfig($key)
    {
        return new self('Viciform config missing required key: ' . $key);
    }

    /**
     * @param string $phone
     * @return self
     */
    public static function invalidPhone($phone)
    {
        return new self('Invalid phone number: ' . $phone . '. Must be 6-16 digits.');
    }

    /**
     * @param int $statusCode
     * @param string $body
     * @return self
     */
    public static function httpError($statusCode, $body = '')
    {
        $snippet = $body !== '' ? ' — ' . substr($body, 0, 200) : '';

        return new self('Vicidial HTTP error ' . $statusCode . $snippet);
    }

    /**
     * @param string $message
     * @return self
     */
    public static function curlError($message)
    {
        return new self('Vicidial cURL error: ' . $message);
    }

    /**
     * @param string $response
     * @return self
     */
    public static function apiError($response)
    {
        return new self('Vicidial API error: ' . $response);
    }
}
