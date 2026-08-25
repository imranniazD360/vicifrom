<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    /** @var string */
    protected $table = 'viciform_centers';

    /** @var array */
    protected $guarded = ['id'];

    /**
     * @param string|null $closerCode first 3 chars of closer, lowercased
     * @return string|null
     */
    public static function matchName($closerCode)
    {
        if ($closerCode === null || $closerCode === '') {
            return null;
        }

        $value = static::query()->where('center_code', $closerCode)->value('center_name');

        return $value === null ? null : (string) $value;
    }
}
