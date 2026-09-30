<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Read/write access to the `settings` table.
 *
 * Values are cached for a short window because the settings form and several
 * document generators read them on every request.
 */
class SettingService
{
    private const CACHE_KEY = 'settings.all';

    private const TTL = 300;

    /**
     * Encode a PHP value for storage according to the setting's declared type.
     */
    public function encode(Setting $setting, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($setting->type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * All settings as key => decoded value.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn(Setting $setting) => [$setting->key => $setting->typedValue()])
                ->all();
        });
    }

    /**
     * Public settings only, for clients that should not see operational values.
     *
     * @return array<string, mixed>
     */
    public function public(): array
    {
        return Cache::remember(self::CACHE_KEY . '.public', self::TTL, function () {
            return Setting::public()
                ->get()
                ->mapWithKeys(fn(Setting $setting) => [$setting->key => $setting->typedValue()])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Persist a batch of values, ignoring keys that are not registered settings.
     *
     * @param  array<string, mixed>  $values
     * @return int Number of settings updated.
     */
    public function updateMany(array $values): int
    {
        $settings = Setting::whereIn('key', array_keys($values))->get();
        $updated = 0;

        foreach ($settings as $setting) {
            $setting->update(['value' => $this->encode($setting, $values[$setting->key])]);
            $updated++;
        }

        $this->flush();

        return $updated;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . '.public');
    }
}
