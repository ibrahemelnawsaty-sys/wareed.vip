<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations;

    public array $translatable = ['value'];

    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * قراءة إعداد حسب اللغة الحالية، مع الرجوع للعربية ثم الافتراضي.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // نخزّن مصفوفة عادية (key => [locale => value]) لتفادي مشاكل تسلسل نماذج Eloquent في الكاش
        $all = Cache::rememberForever('settings.all', fn () => static::all()
            ->mapWithKeys(fn ($s) => [$s->key => $s->getTranslations('value')])
            ->toArray());

        $translations = $all[$key] ?? null;
        if ($translations === null) {
            return $default;
        }

        $locale = app()->getLocale();
        $value = ($translations[$locale] ?? null) ?: ($translations['ar'] ?? null);

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->group = $setting->group ?? $group;
        $setting->type = $setting->type ?? $type;

        if (is_array($value)) {
            // ['ar' => .., 'en' => ..]
            $setting->setTranslations('value', array_filter($value, fn ($v) => $v !== null));
        } else {
            $setting->setTranslation('value', app()->getLocale(), $value);
        }

        $setting->save();
        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
