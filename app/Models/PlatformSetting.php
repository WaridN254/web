<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform_settings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['group', 'key', 'value', 'type', 'options'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;
        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'text'): static
    {
        $existing = static::where('key', $key)->first();
        $group = $existing?->group ?? explode('.', $key)[0] ?? 'general';

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $existing?->type ?? $type, 'group' => $group]
        );
    }

    public static function getMany(array $keys): array
    {
        $settings = static::whereIn('key', $keys)->get()->keyBy('key');
        $result = [];
        foreach ($keys as $key => $default) {
            if (is_int($key)) {
                $result[$default] = $settings->get($default)?->value ?? null;
            } else {
                $result[$key] = $settings->get($key)?->value ?? $default;
            }
        }
        return $result;
    }
}
