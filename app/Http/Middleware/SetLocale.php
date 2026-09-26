<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class SetLocale
{
    public const SUPPORTED = ['ar', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $requested = $request->query('hl');
        // اللغة الافتراضية للزائر الجديد عربية دائماً: الموقع عربي المصدر
        // و`lang/en.json` ترجمة له، فلا يُقرأ app.locale (قيمته en في هيكل لارافل).
        $locale = $requested ?: $request->cookie('locale') ?: 'ar';

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        view()->share('currentLocale', $locale);
        view()->share('isRtl', $locale === 'ar');

        $response = $next($request);

        // تثبيت اختيار اللغة في كوكي لمدة سنة عند التبديل عبر ?hl=
        if ($requested && in_array($requested, self::SUPPORTED, true)) {
            $response->headers->setCookie(
                Cookie::create('locale', $locale, now()->addYear())
            );
        }

        return $response;
    }

    /**
     * رابط الصفحة بلغة معيّنة كما يقرؤه هذا الوسيط: العربية — لغة الزائر
     * الجديد — بلا معامل، وغيرها بـ?hl=. وهو الرابط القانوني (canonical) لنسخة
     * تلك اللغة، فلا تحيل النسخة الإنجليزية إلى العربية أصلاً لها فيُسقطها
     * Google من الفهرسة كنسخة مكرّرة.
     */
    public static function localizedUrl(string $url, string $locale): string
    {
        if ($locale === 'ar') {
            return $url;
        }

        // جذر الموقع بلا مسار يُكتب /?hl= كما يكتبه مبدّل اللغة في الترويسة
        $separator = str_contains($url, '?') ? '&' : (parse_url($url, PHP_URL_PATH) ? '?' : '/?');

        return $url.$separator.'hl='.$locale;
    }
}
