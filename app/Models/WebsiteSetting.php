<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model {
    protected $fillable = ['key','value','type','section'];

    public static function get(string $key, $default = null): mixed
    {
        $settings = Cache::remember('website_settings', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, $value, string $type = 'text', string $section = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'section' => $section]);
        Cache::forget('website_settings');
    }

    public static function setMany(array $data, string $section = 'general'): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value, 'text', $section);
        }
        Cache::forget('website_settings');
    }
}
