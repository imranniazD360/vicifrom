<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    /** @var string */
    protected $table = 'viciform_dialers';

    /** @var array */
    protected $guarded = ['id'];

    /**
     * @param string|null $serverIp
     * @return string|null
     */
    public static function matchNo($serverIp)
    {
        if ($serverIp === null || $serverIp === '') {
            return null;
        }

        $value = static::query()->where('dialer_ip', $serverIp)->value('dialer_no');

        return $value === null ? null : (string) $value;
    }
}
