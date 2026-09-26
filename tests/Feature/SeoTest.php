<?php

use App\Http\Middleware\SetLocale;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * كل نسخة لغوية صفحةٌ قائمة بذاتها في فهرس Google: رابطها القانوني
 * (canonical) هو رابطها هي، وروابط hreflang في النسختين مجموعة واحدة
 * متبادلة. لو أحالت النسخة الإنجليزية إلى العربية أصلاً لعدّها Google
 * نسخة مكرّرة وأسقطها من نتائج البحث.
 */

/** وسوم الرأس التي يقرأ منها Google نسخ الصفحة. */
function seoHead(string $html): array
{
    preg_match('/<link rel="canonical" href="([^"]*)">/', $html, $canonical);
    preg_match('/<meta property="og:url" content="([^"]*)">/', $html, $ogUrl);
    preg_match_all('/<link rel="alternate" hreflang="([^"]+)" href="([^"]*)">/', $html, $links, PREG_SET_ORDER);

    $decode = fn (string $url) => html_entity_decode($url, ENT_QUOTES);

    return [
        'canonical' => $decode($canonical[1] ?? ''),
        'og:url' => $decode($ogUrl[1] ?? ''),
        'alternates' => collect($links)->mapWithKeys(fn ($l) => [$l[1] => $decode($l[2])])->all(),
    ];
}

it('declares each language version as its own canonical page', function () {
    $root = url('/');

    $ar = seoHead($this->get('/')->assertOk()->getContent());

    expect($ar['canonical'])->toBe($root)
        ->and($ar['og:url'])->toBe($root)
        ->and($ar['alternates'])->toBe(['ar' => $root, 'en' => $root.'/?hl=en', 'x-default' => $root]);

    $en = seoHead($this->get('/?hl=en')->assertOk()->getContent());

    expect($en['canonical'])->toBe($root.'/?hl=en')
        ->and($en['og:url'])->toBe($root.'/?hl=en')
        // hreflang متبادل: النسختان تعلنان المجموعة نفسها
        ->and($en['alternates'])->toBe($ar['alternates']);
});

it('keeps the page path in every language link of an inner page', function () {
    Service::create(['key' => 'ecommerce', 'slug' => 'ecommerce', 'is_active' => true, 'icon' => '🛒',
        'name' => ['ar' => 'المتاجر الإلكترونية', 'en' => 'Online stores'],
        'summary' => ['ar' => 'متجرك من ضغطة زر', 'en' => 'Your store in one click'],
    ]);
    $page = url('/services/ecommerce');

    $en = seoHead($this->get('/services/ecommerce?hl=en')->assertOk()->getContent());

    expect($en['canonical'])->toBe($page.'?hl=en')
        ->and($en['alternates'])->toBe(['ar' => $page, 'en' => $page.'?hl=en', 'x-default' => $page]);

    expect(seoHead($this->get('/services/ecommerce')->assertOk()->getContent())['canonical'])->toBe($page);
});

it('drops tracking parameters from canonical and hreflang links', function () {
    $root = url('/');

    $en = seoHead($this->get('/?utm_source=facebook&hl=en')->assertOk()->getContent());

    expect($en['canonical'])->toBe($root.'/?hl=en')
        ->and($en['alternates']['en'])->toBe($root.'/?hl=en');

    $ar = seoHead($this->get('/?utm_source=facebook')->assertOk()->getContent());

    expect($ar['canonical'])->toBe($root)
        ->and($ar['alternates']['en'])->toBe($root.'/?hl=en');
});

it('follows the language actually served, whatever selected it', function () {
    $root = url('/');

    // ‏?hl=ar واللغة غير المدعومة تُخدَم بالعربية، فأصلهما الرابط بلا معامل
    expect(seoHead($this->get('/?hl=ar')->assertOk()->getContent())['canonical'])->toBe($root);
    expect(seoHead($this->get('/?hl=fr')->assertOk()->getContent())['canonical'])->toBe($root);

    // زائر اختار الإنجليزية سابقاً (كوكي) يرى الإنجليزية، فأصل ما يراه النسخة الإنجليزية
    expect(seoHead($this->withCookie('locale', 'en')->get('/')->assertOk()->getContent())['canonical'])
        ->toBe($root.'/?hl=en');
});

it('links the language switcher to the canonical English page', function () {
    // مبدّل اللغة رابط ظاهر يزحف منه Google إلى النسخة الإنجليزية نفسها
    $this->get('/')->assertOk()->assertSee('<a href="'.url('/').'/?hl=en" class="lang-btn"', false);
});

it('builds each language URL the way SetLocale reads it', function () {
    expect(SetLocale::localizedUrl('https://wareed.vip', 'ar'))->toBe('https://wareed.vip')
        ->and(SetLocale::localizedUrl('https://wareed.vip', 'en'))->toBe('https://wareed.vip/?hl=en')
        ->and(SetLocale::localizedUrl('https://wareed.vip/', 'en'))->toBe('https://wareed.vip/?hl=en')
        ->and(SetLocale::localizedUrl('https://wareed.vip/services/training', 'en'))->toBe('https://wareed.vip/services/training?hl=en')
        ->and(SetLocale::localizedUrl('https://wareed.vip/x?id=7', 'en'))->toBe('https://wareed.vip/x?id=7&hl=en');
});
