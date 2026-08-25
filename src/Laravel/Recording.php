<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    /** @var string */
    protected $table = 'viciform_recordings';

    /** @var array */
    protected $guarded = ['id'];
}
