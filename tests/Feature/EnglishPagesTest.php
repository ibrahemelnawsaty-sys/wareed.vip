<?php

use App\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * النسخة الإنجليزية من الموقع صفحات مستقلة في فهرس Google، فأي نص عربي
 * متبقٍّ فيها يراه الزائر الإنجليزي ويظهر في نتائج البحث. تُفحص الصفحات
 * ببيانات التثبيت الأولى نفسها (DatabaseSeeder).
 */

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

/** ما يراه الزائر: متن الصفحة وعنوانها ووسوم وصفها وتلميحات الحقول — بلا سكربتات ولا رسوم. */
function visibleText(string $html): string
{
    // مبدّل اللغة يعرض «ع» عمداً: هو زرّ الانتقال إلى العربية
    $html = preg_replace('#<a [^>]*class="lang-btn".*?</a>#su', '', $html);
    $html = preg_replace('#<(script|style|svg)\b.*?</\1>#su', '', $html);

    preg_match_all('#\b(?:placeholder|title|alt|aria-label)="([^"]*)"#u', $html, $attributes);
    preg_match_all('#<meta (?:name="description"|property="og:\w+"|name="twitter:\w+") content="([^"]*)"#u', $html, $metas);

    return html_entity_decode(strip_tags($html).' '.implode(' ', [...$attributes[1], ...$metas[1]]), ENT_QUOTES);
}

/** العبارات العربية في نص، كلٌّ منها كما ظهرت، ليكون فشل الاختبار دالّاً على مكانها. */
function arabicPhrases(string $text): array
{
    preg_match_all('/\p{Arabic}+(?:[\s\p{P}\d]*\p{Arabic}+)*/u', $text, $matches);

    return array_values(array_unique($matches[0]));
}

it('shows no Arabic text on any public English page', function (string $path) {
    expect(arabicPhrases(visibleText($this->get($path)->assertOk()->getContent())))->toBe([]);
})->with([
    'home' => '/?hl=en',
    'online stores service' => '/services/ecommerce?hl=en',
    'tech solutions service' => '/services/solutions?hl=en',
    'training service' => '/services/training?hl=en',
    'stores' => '/stores?hl=en',
    'store' => '/store/noor-store?hl=en',
    'product' => '/store/noor-store/product/sneakers?hl=en',
    'about' => '/about?hl=en',
    'contact' => '/contact?hl=en',
]);

it('keeps an empty content page English', function () {
    Page::create(['slug' => 'careers', 'status' => 'published', 'content' => [],
        'title' => ['ar' => 'الوظائف', 'en' => 'Careers']]);

    expect(arabicPhrases(visibleText($this->get('/careers?hl=en')->assertOk()->getContent())))->toBe([]);
});

it('shows service form choices in English while submitting the stored Arabic values', function () {
    // القيمة المُرسَلة تبقى عربية كما يقرؤها الفريق في اللوحة وفي البريد الإلكتروني،
    // والمترجَم هو النص المعروض فقط
    $this->get('/services/ecommerce?hl=en')->assertOk()
        ->assertSee('<option value="ملابس وأزياء" >Fashion &amp; apparel</option>', false)
        ->assertSee('Business type');

    // والزائر العربي يرى الحقول كما هي
    $this->get('/services/ecommerce')->assertOk()
        ->assertSee('<option value="ملابس وأزياء" >ملابس وأزياء</option>', false)
        ->assertSee('نوع النشاط');
});

it('confirms every submitted form in the visitor language', function () {
    $contact = ['name' => 'Sara', 'phone' => '01000000000'];

    $this->from('/contact')->post('/contact', $contact)
        ->assertSessionHas('success', 'تم استلام رسالتك بنجاح! سيتواصل معك فريق وريد قريباً.');

    // زائر النسخة الإنجليزية يحمل كوكي اللغة من زيارته ?hl=en
    $this->withCookie('locale', 'en');

    $this->from('/contact?hl=en')->post('/contact', $contact)
        ->assertRedirect('/contact?hl=en')
        ->assertSessionHas('success', 'Your message has been received! The Wareed team will contact you soon.');

    $this->from('/services/training?hl=en')->post('/services/training/request', $contact)
        ->assertSessionHas('success', 'Your request has been received! The Wareed team will contact you within hours.');

    $this->post('/store/noor-store/product/sneakers/order', [
        'customer_name' => 'Sara', 'customer_phone' => '01000000000', 'shipping_address' => '12 Nile St, Cairo',
    ])->assertSessionHas('success', fn (string $message) => preg_match(
        '/^Your order WRD-[A-Z0-9]{8} has been received! We’ll contact you to confirm it and arrange cash on delivery\.$/u', $message
    ) === 1);
});

it('has an English translation for every literal text in the public views and controllers', function () {
    $en = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

    $files = [
        ...glob(resource_path('views/{home,contact}.blade.php'), GLOB_BRACE),
        ...glob(resource_path('views/{layouts,partials,pages,services,stores,components}/*.blade.php'), GLOB_BRACE),
        app_path('Http/Controllers/PageController.php'),
        app_path('Http/Controllers/ServiceController.php'),
        app_path('Http/Controllers/StoreController.php'),
    ];

    $missing = [];
    foreach ($files as $file) {
        preg_match_all("/__\(\s*'((?:[^'\\\\]|\\\\.)*)'/u", file_get_contents($file), $matches);
        foreach ($matches[1] as $key) {
            $key = stripslashes($key);
            if (preg_match('/\p{Arabic}/u', $key) && ! array_key_exists($key, $en)) {
                $missing[] = basename($file).': '.$key;
            }
        }
    }

    expect($missing)->toBe([]);
});
