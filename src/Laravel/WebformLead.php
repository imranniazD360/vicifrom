<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Illuminate\Database\Eloquent\Model;

/**
 * Persisted Vicidial webform / ParameterController lead row.
 *
 * Table: viciform_webform_leads
 */
class WebformLead extends Model
{
    /** @var string */
    protected $table = 'viciform_webform_leads';

    /** @var array */
    protected $guarded = ['id'];

    /** @var array */
    protected $casts = [
        'extras' => 'array',
    ];

    /**
     * Create from ParameterBridge::forStore() / ScriptPayload.
     *
     * @param array<string, mixed> $attributes
     * @return self
     */
    public static function fromWebform(array $attributes)
    {
        $extras = null;
        if (isset($attributes['extras']) && is_array($attributes['extras'])) {
            $extras = $attributes['extras'];
        }

        // Prefer recording_link; keep recordingLink alias out of DB if duplicate
        if (isset($attributes['recordingLink']) && empty($attributes['recording_link'])) {
            $attributes['recording_link'] = $attributes['recordingLink'];
        }
        unset($attributes['recordingLink']);

        if ($extras !== null) {
            $attributes['extras'] = $extras;
        }

        return static::query()->create($attributes);
    }
}
