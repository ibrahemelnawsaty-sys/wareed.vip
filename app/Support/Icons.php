<?php

namespace App\Support;

/**
 * نظام الأيقونات الموحّد لواجهة وريد.
 *
 * كل الأيقونات رسوم SVG خطّية بشبكة 24×24 وسماكة واحدة، تأخذ لونها من
 * currentColor فتتبع ألوان الهوية أينما وُضعت — لا إيموجي في أي شاشة.
 *
 * أيقونات الخدمات ومميّزاتها مخزّنة في قاعدة البيانات كإيموجي (بيانات إنتاج
 * قائمة لا تُعدَّل من هنا)، فتُترجم عند العرض عبر MAP إلى اسم أيقونة رسمية.
 * أي قيمة غير معروفة تسقط على أيقونة افتراضية بدل أن تظهر كمربّع فارغ.
 */
final class Icons
{
    /** ترجمة قيم الإيموجي المخزّنة في قاعدة البيانات إلى أسماء أيقونات النظام. */
    public const MAP = [
        // الخدمات الثلاث
        '🛒' => 'store',
        '🧩' => 'layers',
        '🎓' => 'academy',

        // مميّزات المتاجر الإلكترونية
        '⚡' => 'bolt',
        '💳' => 'card',
        '🚚' => 'truck',
        '🔍' => 'search',
        '📊' => 'chart',
        '🛡️' => 'shield',
        '🛡' => 'shield',

        // مميّزات الحلول الرقمية
        '🔎' => 'search',
        '⚙️' => 'settings',
        '⚙' => 'settings',
        '🏛️' => 'bank',
        '🏛' => 'bank',
        '🔐' => 'lock',
        '☁️' => 'cloud',
        '☁' => 'cloud',
        '🤝' => 'support',

        // مميّزات البرامج التدريبية
        '🤖' => 'cpu',
        '🧱' => 'server',
        '🎨' => 'palette',
        '🗄️' => 'database',
        '🗄' => 'database',
        '🖥️' => 'monitor',
        '🖥' => 'monitor',
        '👨‍🏫' => 'presenter',
        '👩‍🏫' => 'presenter',

        // شائعة أخرى قد ترد من لوحة التحكم
        '📈' => 'trend',
        '📱' => 'mobile',
        '💡' => 'idea',
        '🚀' => 'rocket',
        '👥' => 'users',
        '🎯' => 'target',
        '📦' => 'box',
        '✉️' => 'mail',
        '📧' => 'mail',
        '📞' => 'phone',
        '📍' => 'pin',
        '⏱️' => 'clock',
        '🕐' => 'clock',
        '✦' => 'spark',
        '◆' => 'spark',
        '✓' => 'check',
    ];

    /** أسماء الأيقونات المتاحة فعلياً في الملصقة (partials/icons). */
    public const AVAILABLE = [
        'store', 'layers', 'academy', 'bolt', 'card', 'truck', 'search', 'chart',
        'shield', 'settings', 'bank', 'lock', 'cloud', 'support', 'cpu', 'server',
        'palette', 'database', 'monitor', 'presenter', 'trend', 'mobile', 'idea',
        'rocket', 'users', 'target', 'box', 'mail', 'phone', 'pin', 'clock',
        'spark', 'check', 'arrow-left', 'arrow-down', 'chevron-down', 'plus',
        'minus', 'menu', 'close', 'globe', 'whatsapp', 'code', 'sparkles',
    ];

    /**
     * اسم الأيقونة المناسب لقيمة مخزّنة: يقبل الإيموجي القديم أو اسم أيقونة صريحاً.
     * لا يعيد أبداً قيمة غير موجودة في الملصقة.
     */
    public static function name(?string $raw, string $fallback = 'spark'): string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return $fallback;
        }

        if (in_array($raw, self::AVAILABLE, true)) {
            return $raw;
        }

        if (isset(self::MAP[$raw])) {
            return self::MAP[$raw];
        }

        // إيموجي مركّب (بمُعدِّل لون أو ZWJ) — نجرّب أول محرف أساسي منه
        $base = preg_replace('/[\x{FE0F}\x{200D}\x{1F3FB}-\x{1F3FF}].*$/u', '', $raw);

        return self::MAP[$base] ?? $fallback;
    }

    /** أيقونة الخدمة حسب مفتاحها — تُستخدم حين لا تحمل الخدمة أيقونة صالحة. */
    public static function forServiceKey(?string $key): string
    {
        return match ($key) {
            'ecommerce' => 'store',
            'tech_solution', 'solutions' => 'layers',
            'training' => 'academy',
            default => 'spark',
        };
    }
}
