<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * قراءة إعداد عام قابل للتحرير من لوحة الأدمن.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /**
     * تنسيق مبلغ مخزّن بالقروش (عدد صحيح) إلى جنيه مصري. null-safe (دستور §3).
     */
    function money(?int $minorUnits, string $currency = '$'): string
    {
        $value = ($minorUnits ?? 0) / 100;
        $formatted = number_format($value, $value == (int) $value ? 0 : 2);

        return $currency.$formatted;
    }
}

if (! function_exists('tleaf')) {
    /**
     * يختار القيمة حسب اللغة الحالية من ورقة ثنائية اللغة ['ar'=>..,'en'=>..]،
     * أو يُرجع القيمة كما هي إن لم تكن مترجمة. يدعم النصوص والمصفوفات.
     */
    function tleaf(mixed $value, mixed $default = null): mixed
    {
        if (is_array($value) && (isset($value['ar']) || isset($value['en']))) {
            $locale = app()->getLocale();

            return $value[$locale] ?? $value['ar'] ?? $value['en'] ?? $default;
        }

        return $value ?? $default;
    }
}

if (! function_exists('wareed_phone')) {
    function wareed_phone(): string
    {
        return (string) setting('contact_phone', '+20 100 000 0000');
    }
}
