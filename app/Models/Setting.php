<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'attributes';

    protected $fillable = [
        'key',
        'value',
        'attributable_id',
        'attributable_type',
    ];

    public const SCOPE_TYPE = 'App\\Setting';

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $value = static::query()
            ->where('key', $key)
            ->whereNull('attributable_id')
            ->whereIn('attributable_type', [
                self::SCOPE_TYPE,
                'App\\Setting',
                'App\\Models\\Setting',
                'setting',
            ])
            ->orderByRaw("attributable_type = ? desc", [self::SCOPE_TYPE])
            ->value('value');

        return $value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            [
                'attributable_type' => self::SCOPE_TYPE,
                'attributable_id' => null,
                'key' => $key,
            ],
            [
                'value' => (string) ($value ?? ''),
            ],
        );
    }
}