<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'json'];

    /**
     * PERF-02 FIX: Cache individual settings for 5 minutes so kiosk scans
     * (which call get() twice per scan for cooldown_minutes and kiosk_mode)
     * no longer hit the database on every request.
     *
     * Cache is automatically busted by set() so stale values are never served.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting_{$key}", 300, function () use ($key, $default) {
            $row = static::query()->find($key);
            return $row ? $row->value : $default;
        });
    }

    /**
     * Persist a setting and immediately bust its cache entry so the next
     * get() call reflects the new value without waiting for TTL expiry.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        // PERF-02 FIX: Bust the individual key cache on write
        Cache::forget("system_setting_{$key}");
        // BUG-NEW-01 FIX: Also bust the allSettings() cache so KioskController
        // and SettingsController pick up changes without waiting for TTL expiry.
        Cache::forget('system_settings_all');
    }

    /**
     * BUG-NEW-01 FIX: Cache allSettings() for 5 minutes.
     * Previously ran a full table SELECT on every kiosk page load.
     * set() busts this cache so updates are always reflected immediately.
     */
    public static function allSettings(): array
    {
        return Cache::remember('system_settings_all', 300, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }
}
