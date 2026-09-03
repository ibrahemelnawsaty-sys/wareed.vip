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
     * قراءة إعداد حسب اللغة الحالية.
     *
     * الترتيب: لغة الزائر ← العربية ← الإنجليزية ← أي ترجمة محفوظة ← الافتراضي.
     * الرجوع إلى «أي ترجمة محفوظة» ضروري: الإعداد يُحفظ باللغة النشطة وقت الحفظ
     * من اللوحة، فلو حُفظ بالإنجليزية وزار الموقعَ زائر عربي لاختفت القيمة
     * تماماً (اسم الموقع، البريد الإلكتروني، الرقم الضريبي، بيانات البنك…).
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
        $candidates = array_merge(
            [$translations[$locale] ?? null, $translations['ar'] ?? null, $translations['en'] ?? null],
            array_values($translations),
        );

        foreach ($candidates as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
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
