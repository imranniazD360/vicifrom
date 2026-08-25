<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Viciform\Response addLead(mixed $lead)
 * @method static \Viciform\Response updateLead(array $data)
 * @method static \Viciform\Response call(string $function, array $params = [])
 * @method static \Viciform\Config config()
 *
 * @see \Viciform\Client
 */
class ViciformFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Viciform\Client::class;
    }
}
