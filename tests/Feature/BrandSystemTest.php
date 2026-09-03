<?php

use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Support\Icons;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Service::create(['key' => 'ecommerce', 'slug' => 'ecommerce', 'is_active' => true, 'icon' => '🛒',
        'name' => ['ar' => 'المتاجر الإلكترونية'], 'summary' => ['ar' => 'متجرك من ضغطة زر'],
        'features' => [['icon' => '⚡', 'title' => ['ar' => 'تجهيز فوري'], 'desc' => ['ar' => 'خلال دقائق']]],
        'faqs' => [['q' => ['ar' => 'سؤال؟'], 'a' => ['ar' => 'جواب.']]],
        'form_fields' => [['name' => 'business_type', 'label' => 'نوع النشاط', 'type' => 'select', 'options' => ['أزياء', 'أخرى']]],
    ]);
    Service::create(['key' => 'training', 'slug' => 'training', 'is_active' => true, 'icon' => '🎓',
        'name' => ['ar' => 'التدريب التقني'], 'summary' => ['ar' => 'برامج تدريبية'],
        'features' => [['icon' => '🤖', 'title' => ['ar' => 'الذكاء الاصطناعي'], 'desc' => ['ar' => 'تعلّم الآلة']]],
    ]);
});

/*
 * نظام الهوية البصرية: الأيقونات الرسمية بدل الإيموجي، وخط ثمانية،
 * والعربية لغةً افتراضية للزائر الجديد.
 */

it('maps every stored service emoji to an icon that exists in the sprite', function () {
    foreach (Icons::MAP as $emoji => $name) {
        expect(Icons::AVAILABLE)->toContain($name);
        expect($emoji)->toBeString();
    }
});

it('never returns an icon name that is missing from the sprite', function () {
    $inputs = ['', null, '🛒', '🎓', '👨‍🏫', '🛡️', 'store', 'مجهول', '💀', '◆'];

    foreach ($inputs as $input) {
        expect(Icons::AVAILABLE)->toContain(Icons::name($input));
    }
});

it('falls back to the service key icon when the stored icon is unknown', function () {
    expect(Icons::name('💀', Icons::forServiceKey('ecommerce')))->toBe('store')
        ->and(Icons::name(null, Icons::forServiceKey('training')))->toBe('academy')
        ->and(Icons::name('', Icons::forServiceKey('tech_solution')))->toBe('layers')
        ->and(Icons::forServiceKey('unknown-key'))->toBe('spark');
});

it('strips emoji modifiers before looking the icon up', function () {
    // ‏\u{FE0F} محدِّد العرض التصويري يلتصق بكثير من الإيموجي المخزّن
    expect(Icons::name("\u{1F6E1}\u{FE0F}"))->toBe('shield')
        ->and(Icons::name("\u{2601}\u{FE0F}"))->toBe('cloud');
});

it('renders the landing pages with icons and without a single emoji', function () {
    $urls = ['/', '/contact'];

    foreach (Service::query()->where('is_active', true)->pluck('slug') as $slug) {
        $urls[] = '/services/'.$slug;
    }

    foreach ($urls as $url) {
        $html = $this->get($url)->assertOk()->getContent();

        // نصّ الصفحة المرئي فقط: نحذف الوسوم ثم نبحث عن أي إيموجي
        $text = preg_replace('/<(script|style|svg)\b.*?<\/\1>/is', ' ', $html);
        $text = strip_tags($text);

        expect(preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}]/u', $text))
            ->toBe(0, "بقي إيموجي في {$url}");
        expect($html)->toContain('<svg class="ic"');
    }
});

it('serves the self-hosted Thmanyah weights the stylesheet declares', function () {
    foreach (['Light', 'Regular', 'Medium', 'Bold', 'Black'] as $weight) {
        expect(public_path("fonts/thmanyah/ThmanyahSans-{$weight}.woff2"))->toBeFile();
    }

    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain("font-family: 'Thmanyah'")
        ->and($css)->not->toContain('fonts.bunny.net');

    $home = $this->get('/')->assertOk()->getContent();

    expect($home)->toContain('fonts/thmanyah/ThmanyahSans-Regular.woff2');
    expect(str_contains($home, 'fonts.bunny.net'))->toBeFalse();
});

it('serves Arabic to a first-time visitor and honours the language switch', function () {
    $this->get('/')->assertOk()->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false);

    $this->get('/?hl=en')->assertOk()->assertSee('lang="en"', false)->assertSee('dir="ltr"', false);

    // اختيار اللغة يبقى مثبّتاً في كوكي
    $this->withCookie('locale', 'en')->get('/')->assertOk()->assertSee('lang="en"', false);
});

it('keeps a setting readable for visitors of another language', function () {
    // الإعداد يُحفظ بالإنجليزية من اللوحة، ثم يزور الموقعَ زائر عربي
    app()->setLocale('en');
    Setting::set('contact_email', 'info@wareed.vip');

    app()->setLocale('ar');
    expect(setting('contact_email'))->toBe('info@wareed.vip');

    // وترجمة اللغة نفسها تظل مقدَّمة على البديل
    Setting::set('contact_email', 'ar@wareed.vip');
    expect(setting('contact_email'))->toBe('ar@wareed.vip');

    app()->setLocale('en');
    expect(setting('contact_email'))->toBe('info@wareed.vip');
});

it('keeps the printed proposal page geometry outside the print media query', function () {
    // ‏beforeprint يسبق تطبيق أنماط الطباعة، فلو كانت مقاسات الصفحة داخل
    // ‏@media print لقاس المقسّم صفحاتٍ بارتفاع صفر وطبع المستند ناقصاً بلا إنذار.
    $css = file_get_contents(resource_path('views/quote/proposal.blade.php'));

    $geometry = 'body.paged .pg {';
    $printAt = strpos($css, '@media print {');
    $geometryAt = strpos($css, $geometry);

    expect($geometryAt)->not->toBeFalse()
        ->and($printAt)->not->toBeFalse()
        ->and($geometryAt)->toBeLessThan($printAt);

    // والارتفاع الثابت هو ما يجعل fits() ذا معنى
    $block = substr($css, $geometryAt, strpos($css, '}', $geometryAt) - $geometryAt);
    expect($block)->toContain('height: 297mm')->toContain('width: 210mm');

    // وعلى الشاشة تُقاس دون أن تُرى
    expect($css)->toContain('body.paged .pg { position: fixed;');
});

it('keeps internal payload keys and nested values out of the notification email', function () {
    Mail::fake();

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce',
        'name' => 'أ. هاجر سلامة',
        'phone' => '00201016031031',
        'email' => 'client@example.com',
        'status' => 'new',
        'payload' => [
            'مجال المتجر' => 'هدايا وإكسسوارات',
            'الخدمات المطلوبة' => ['تجهيز المتجر', 'تصوير المنتجات'],
            'حقل فارغ' => '',
            // بيانات داخلية: عرض السعر ومسار المراحل وقرار العميل والمشاهدات
            '_quote' => ['items' => [['name' => 'بند', 'qty' => 1, 'price' => 100]]],
            '_flow' => ['stage' => 'quote_due', 'meeting_at' => '2026-09-10'],
            '_views' => [['channel' => 'platform', 'at' => '2026-09-10T10:00:00Z']],
        ],
    ]);

    $html = (new ServiceRequestReceived($sr))->render();

    // إجابات العميل تظهر مقروءة، والاختيار المتعدّد مفصولاً
    expect($html)->toContain('هدايا وإكسسوارات')
        ->toContain('تجهيز المتجر، تصوير المنتجات');

    // ولا شيء من البيانات الداخلية ولا أثر لتحويل مصفوفة إلى نص
    foreach (['_quote', '_flow', '_views', '>Array<', 'حقل فارغ'] as $leak) {
        expect(str_contains($html, $leak))->toBeFalse("تسرّب {$leak} إلى بريد الإشعار");
    }
});

it('sends emails free of emoji', function () {
    Mail::fake();

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'عميل', 'phone' => '01000000000',
        'email' => 'c@example.com', 'status' => 'new', 'payload' => ['مجال المتجر' => 'أزياء'],
    ]);

    $mails = [
        (new ServiceRequestReceived($sr))->render(),
        (new ServiceRequestConfirmation($sr))->render(),
    ];

    foreach ($mails as $html) {
        expect(preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', strip_tags($html)))->toBe(0);
    }
});
