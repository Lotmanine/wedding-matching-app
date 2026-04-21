<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function get(string $key, mixed $fallback = null): mixed
    {
        $cached = Cache::rememberForever('settings:all', function () {
            if (! self::tableExists()) {
                return [];
            }

            return self::all()->keyBy('key')->all();
        });

        $row = $cached[$key] ?? null;
        if ($row === null) {
            return $fallback;
        }

        return self::castValue($row->value, $row->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string'): self
    {
        $record = self::updateOrCreate(['key' => $key], [
            'value' => is_array($value) ? json_encode($value) : (string) $value,
            'type'  => $type,
        ]);

        Cache::forget('settings:all');

        return $record;
    }

    private static function castValue(?string $raw, string $type): mixed
    {
        if ($raw === null) {
            return null;
        }

        return match ($type) {
            'int'  => (int) $raw,
            'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($raw, true),
            default => $raw,
        };
    }

    private static function tableExists(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
