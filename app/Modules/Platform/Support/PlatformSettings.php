<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\PlatformSetting;
use Throwable;

/**
 * Switches flipped from the admin panel, with the .env value as the default.
 *
 * A row wins over the .env once it exists; with no row, the .env decides. That
 * keeps a fresh deploy exactly as configured and lets an administrator change
 * it later without touching the server.
 *
 * Not memoised on purpose: a static cache would outlive a test (or a queue
 * worker) and answer with yesterday's value. Callers read it once or twice per
 * request or command, which costs nothing.
 *
 * A missing table (the migration has not run yet) reads as "no row", so a
 * deploy that ships the code before the migration falls back to the .env
 * instead of failing.
 */
class PlatformSettings
{
    public static function row(string $key): ?PlatformSetting
    {
        try {
            return PlatformSetting::query()->with('updatedBy:id,name')->find($key);
        } catch (Throwable) {
            return null;
        }
    }

    public static function bool(string $key, bool $default): bool
    {
        $row = self::row($key);

        return $row === null ? $default : $row->value === '1';
    }

    public static function setBool(string $key, bool $value, ?int $userId): PlatformSetting
    {
        return PlatformSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value ? '1' : '0', 'updated_by' => $userId],
        );
    }
}
