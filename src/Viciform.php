<?php

declare(strict_types=1);

namespace Viciform;

use Viciform\Webform\ScriptPayload;

/**
 * Lightweight static entry point for plain PHP projects.
 *
 * Laravel users should prefer the Viciform facade or dependency injection.
 */
final class Viciform
{
    /** @var Client|null */
    private static $client = null;

    /**
     * @param Config|array $config
     * @return Client
     */
    public static function configure($config)
    {
        self::$client = new Client($config);

        return self::$client;
    }

    /**
     * @return Client
     */
    public static function client()
    {
        if (self::$client === null) {
            throw new Exceptions\ViciformException(
                'Viciform is not configured. Call Viciform::configure([...]) first.'
            );
        }

        return self::$client;
    }

    /**
     * @param Lead|array $lead
     * @return Response
     */
    public static function addLead($lead)
    {
        return self::client()->addLead($lead);
    }

    /**
     * @param array $data
     * @return Response
     */
    public static function updateLead(array $data)
    {
        return self::client()->updateLead($data);
    }

    /**
     * @param string $function
     * @param array $params
     * @return Response
     */
    public static function call($function, array $params = [])
    {
        return self::client()->call($function, $params);
    }

    /**
     * Parse Vicidial campaign-script / Avatar-style webform parameters.
     *
     * Does not require Viciform::configure() — inbound only.
     *
     * @param mixed $request Laravel Request, array, or null for $_GET+$_POST
     * @return ScriptPayload
     */
    public static function webform($request = null)
    {
        if ($request === null) {
            return ScriptPayload::fromGlobals();
        }

        return ScriptPayload::fromRequest($request);
    }

    /**
     * @return void
     */
    public static function reset()
    {
        self::$client = null;
    }
}
