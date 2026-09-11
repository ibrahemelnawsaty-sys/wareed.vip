<?php

use App\Filament\Pages\QuoteRequests;
use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function amountsAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

function amountsRequest(): ServiceRequest
{
    return ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'company' => 'متجر حواديت',
        'phone' => '—', 'email' => 'hagersalma89@gmail.com', 'status' => 'new', 'source' => 'quote_form',
    ]);
}

/** محرّر عرض السعر مفتوحاً على بند واحد بسعر محدَّد وبلا ضريبة. */
function amountsEditor(ServiceRequest $sr, float $price, float $vat = 0)
{
    return Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items', [['phase' => '', 'name' => 'تجهيز المتجر', 'desc' => '', 'note' => '', 'qty' => 1, 'unit' => '', 'price' => $price, 'free' => false]])
        ->set('draft.vat_percent', $vat);
}

it('يقبل الخصم قيمةً أو نسبة، ويحسب الطرف الآخر من المكتوب', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    $page = amountsEditor($sr, 20000);

    // القيمة تُشتقّ منها النسبة
    $page->call('setDiscountAmount', '5000')->assertSet('draft.discount_percent', 25.0);
    expect($page->instance()->draftTotals['discount'])->toBe(5000.0)
        ->and($page->instance()->draftTotals['total'])->toBe(15000.0);

    // والنسبة تُشتقّ منها القيمة
    $page->set('draft.discount_percent', 10);
    expect($page->instance()->draftTotals['discount'])->toBe(2000.0)
        ->and($page->instance()->draftTotals['total'])->toBe(18000.0);

    // القيمة تُقرأ من نصّ الحقل كما يرسله المتصفّح
    $page->call('setDiscountAmount', '7500.50');
    expect($page->instance()->draftTotals['discount'])->toBe(7500.5);
});

it('يعيد قيمة الخصم المكتوبة كما هي حتى القرش مهما كانت النسبة كسرية', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // إجمالي كسري: نسبة الخصم بمنزلتين عشريتين وحدها لا تكفي لإرجاع القيمة المكتوبة
    $page = amountsEditor($sr, 18441.17, 14)->call('setDiscountAmount', '5283.28');

    $totals = $page->instance()->draftTotals;

    expect($totals['discount'])->toBe(5283.28)
        ->and(round($totals['discount_percent'], 2))->toBe(28.65)
        // بنسبة 28.65% وحدها لكان الخصم 5283.4 — فرق يفسد الإجمالي المتفق عليه
        ->and(round(18441.17 * 28.65 / 100, 2))->not->toBe(5283.28);

    // النسبة الدقيقة تبقى محفوظة بعد الإصدار وإعادة فتح المحرّر
    Mail::fake();
    $page->call('issueQuote', false);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['discount'])->toBe(5283.28)
        ->and($quote['discount_percent'])->toBe($totals['discount_percent']);

    $reopened = Livewire::test(QuoteRequests::class)->call('openQuote', $sr->id);
    expect($reopened->instance()->draftTotals['discount'])->toBe(5283.28);
});

it('يقبل الدفعة قيمةً أو نسبة من الإجمالي المستحق', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    $page = amountsEditor($sr, 15000)
        ->set('draft.payments', [
            ['label' => 'الدفعة الأولى', 'note' => '', 'due' => '', 'percent' => 50.0],
            ['label' => 'الدفعة الثانية', 'note' => '', 'due' => '', 'percent' => 50.0],
        ]);

    $page->call('setPaymentAmount', 0, '5250');

    $totals = $page->instance()->draftTotals;
    expect($totals['payments'][0]['percent'])->toBe(35.0)
        ->and($totals['payments'][0]['amount'])->toBe(5250.0)
        // الدفعة الأخرى لا تتأثر، ومجموع النسب يوضّح النقص للفريق
        ->and($totals['payments'][1]['amount'])->toBe(7500.0)
        ->and($totals['payments_percent'])->toBe(85.0);

    // دفعة غير موجودة لا تكسر الصفحة
    $page->call('setPaymentAmount', 9, '1000');
    expect($page->instance()->draftTotals['payments'])->toHaveCount(2);
});

it('يقبل خصم الباقات الاختيارية قيمةً أو نسبة', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    $page = amountsEditor($sr, 20000)
        ->set('draft.extras', [['name' => 'باقة تسويق', 'desc' => '', 'note' => '', 'qty' => 1, 'unit' => '', 'price' => 1000]])
        ->set('draft.extras_vat_percent', 0)
        ->call('setExtrasDiscountAmount', '250');

    expect($page->get('draft.extras_discount_percent'))->toBe(25.0)
        ->and($page->instance()->draftTotals['extras_discount'])->toBe(250.0)
        ->and($page->instance()->draftTotals['extras_total'])->toBe(750.0)
        // خصم الباقات لا يمسّ الإجمالي المستحق
        ->and($page->instance()->draftTotals['total'])->toBe(20000.0);
});

it('يحمي حساب النسبة من الصفر والقيم الخارجة عن الحدّ', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // بلا بنود مسعّرة: القيمة لا تُنتج نسبة (لا قسمة على صفر)
    $page = amountsEditor($sr, 0)->call('setDiscountAmount', '500');
    expect($page->get('draft.discount_percent'))->toEqual(0);

    $page->call('setExtrasDiscountAmount', '500')->call('setPaymentAmount', 0, '500');
    expect($page->get('draft.extras_discount_percent'))->toEqual(0);

    // قيمة أكبر من الإجمالي تقف عند 100%، والسالبة عند الصفر
    $page->set('draft.items.0.price', 10000)->call('setDiscountAmount', '99999');
    expect($page->get('draft.discount_percent'))->toEqual(100)
        ->and($page->instance()->draftTotals['total'])->toEqual(0);

    $page->call('setDiscountAmount', '-50');
    expect($page->get('draft.discount_percent'))->toEqual(0);

    $page->call('setDiscountAmount', 'نص');
    expect($page->get('draft.discount_percent'))->toEqual(0);
});

it('يشتقّ نسبة الخصم من العروض القديمة المخزّنة بقيمة مباشرة بدقّة تطابق القيمة المطبوعة', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // عرض بالبنية القديمة: قيمة خصم مباشرة بلا نسبة — كما في العروض الصادرة قبل تحويل الخصم لنسبة
    $sr->update(['payload' => [
        '_quote' => [
            'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 18441.17]],
            'discount' => 5283.2753, 'vat_percent' => 14, 'currency' => 'ج.م',
            'issued_at' => now()->subDays(2)->toIso8601String(), 'version' => 1,
        ],
    ]]);

    $quote = QuoteController::quoteOf($sr->fresh());
    $shown = rtrim(rtrim(number_format($quote['discount_percent'], 4), '0'), '.');

    expect($shown)->toBe('28.6494')
        // النسبة المعروضة تُعيد قيمة الخصم المعروضة — وبمنزلتين (28.65%) كانت تعطي 5,283.40
        ->and(round(18441.17 * (float) $shown / 100, 2))->toBe(round($quote['discount'], 2))
        ->and(round($quote['discount'], 2))->toBe(5283.28);

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('الخصم (28.6494%)')
        ->assertSee('5,283.28');
});

it('يحرّر سجلّ الإصدارات فيُكمل الفريق إصداراً فات تسجيله ويظهر للعميل مرتّباً', function () {
    Mail::fake();
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // حال العروض التي صدرت قبل تفعيل السجلّ: الإصدار الحالي 3 وفي السجلّ الإصدار 2 وحده
    $sr->update(['payload' => [
        '_quote' => [
            'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 20000]],
            'discount_percent' => 25, 'vat_percent' => 0, 'currency' => 'ج.م',
            'issued_at' => now()->toIso8601String(), 'version' => 3,
            'history' => [[
                'version' => 2, 'issued_at' => now()->subDay()->toIso8601String(),
                'subtotal' => 20000.0, 'discount_percent' => 10.0, 'discount' => 2000.0,
                'vat_percent' => 0.0, 'vat' => 0.0, 'total' => 18000.0, 'currency' => 'ج.م',
            ]],
        ],
    ]]);

    expect(QuoteController::quoteOf($sr->fresh())['versions'])->toHaveCount(2);

    $page = Livewire::test(QuoteRequests::class)->call('openQuote', $sr->id);

    // لقطة إصدار قديم بنسبة مقرَّبة لمنزلتين تخالف قيمتها: القيمة هي المرجع فلا تتغيّر بالحفظ
    $page->set('draft.history.0.subtotal', 18441.17)
        ->set('draft.history.0.discount_percent', 28.65)
        ->call('setHistoryDiscountAmount', 0, '5283.28')
        ->call('issueQuote', false);

    $kept = QuoteController::quoteOf($sr->fresh())['history'][0];
    expect($kept['discount'])->toBe(5283.28)
        ->and($kept['after_discount'])->toBe(13157.89);

    Livewire::test(QuoteRequests::class)->call('openQuote', $sr->id)->call('issueQuote', false);
    expect(QuoteController::quoteOf($sr->fresh())['history'][0]['discount'])->toBe(5283.28);

    $page = Livewire::test(QuoteRequests::class)->call('openQuote', $sr->id);

    // السجلّ يُحمَّل للتحرير بتاريخ صالح لحقل التاريخ
    expect($page->get('draft.history'))->toHaveCount(1)
        ->and($page->get('draft.history.0.version'))->toEqual(2)
        ->and($page->get('draft.history.0.issued_at'))->toBe(now()->subDay()->toDateString());

    // إضافة الإصدار الناقص: يبدأ بأصغر رقم غير مستخدم
    $page->call('addHistory');
    expect($page->get('draft.history.1.version'))->toEqual(1);

    $page->set('draft.history.1.issued_at', now()->subDays(5)->toDateString())
        ->set('draft.history.1.subtotal', 26220)
        ->call('setHistoryDiscountAmount', 1, '2622')
        ->call('issueQuote', false);

    // الإصدار لم يزد ولم تُضف لقطة تلقائية عند الحفظ دون إرسال
    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['version'])->toBe(3)
        ->and($quote['history'])->toHaveCount(2)
        // مرتّب برقم الإصدار لا بترتيب الإضافة
        ->and(array_column($quote['versions'], 'version'))->toBe([1, 2, 3])
        ->and($quote['versions'][0])->toMatchArray([
            'version' => 1, 'subtotal' => 26220.0, 'discount_percent' => 10.0,
            'discount' => 2622.0, 'after_discount' => 23598.0, 'total' => 23598.0, 'current' => false,
        ])
        ->and($quote['versions'][0]['issued_at']->toDateString())->toBe(now()->subDays(5)->toDateString())
        ->and($quote['versions'][2]['current'])->toBeTrue();

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('سجلّ إصدارات العرض')
        ->assertSee('26,220')
        ->assertSee('23,598');

    // حذف صفّ من السجلّ، ثم إعادة إصدار فعلية تضيف لقطة الإصدار الحالي تلقائياً فوق المحرَّر
    $page = Livewire::test(QuoteRequests::class)->call('openQuote', $sr->id)->call('removeHistory', 0);
    expect($page->get('draft.history'))->toHaveCount(1);

    $page->call('issueQuote', true);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['version'])->toBe(4)
        ->and(array_column($quote['versions'], 'version'))->toBe([1, 3, 4])
        ->and($quote['versions'][1])->toMatchArray(['version' => 3, 'subtotal' => 20000.0, 'discount' => 5000.0, 'total' => 15000.0]);
});

it('يحفظ سجلّ الإصدارات وحده دون أن يزيح أرقام العرض الجاري', function () {
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // عرض بالبنية القديمة (قيمة خصم غير مقرَّبة) إجماليه 15,000.00 بالضبط
    $sr->update(['payload' => ['_quote' => [
        'items' => [['name' => 'تجهيز المتجر', 'qty' => 1, 'price' => 18441.17]],
        'discount' => 5283.2753, 'vat_percent' => 14, 'currency' => 'ج.م',
        'issued_at' => now()->toIso8601String(), 'version' => 3,
        'history' => [[
            'version' => 2, 'issued_at' => now()->subDay()->toIso8601String(),
            'subtotal' => 18441.17, 'discount_percent' => 28.649375, 'discount' => 5283.28,
            'vat_percent' => 14.0, 'vat' => 1842.1, 'total' => 14999.99, 'currency' => 'ج.م',
        ]],
    ]]]);

    $totalBefore = QuoteController::quoteOf($sr->fresh())['total'];
    expect(round($totalBefore, 2))->toBe(15000.0);

    Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.history.0.subtotal', 20900)
        ->call('setHistoryDiscountAmount', 0, '5987.72')
        ->call('addHistory')
        ->set('draft.history.1.version', 1)
        ->set('draft.history.1.issued_at', '2026-09-03')
        ->set('draft.history.1.subtotal', 20900)
        ->set('draft.history.1.vat_percent', 14)
        ->call('setHistoryDiscountAmount', 1, '3527.19')
        ->call('saveHistory');

    $quote = QuoteController::quoteOf($sr->fresh());

    // السجلّ صُحّح وأُكمل
    expect(array_column($quote['versions'], 'version'))->toBe([1, 2, 3])
        ->and($quote['versions'][0])->toMatchArray(['subtotal' => 20900.0, 'discount' => 3527.19, 'total' => 19805.0])
        ->and($quote['versions'][1])->toMatchArray(['subtotal' => 20900.0, 'discount' => 5987.72, 'total' => 17000.0])
        // والعرض الجاري لم يُمسّ: بنية الخصم القديمة وإجماليه كما هما
        ->and(($sr->fresh()->payload['_quote']['discount'] ?? null))->toBe(5283.2753)
        ->and($sr->fresh()->payload['_quote']['discount_percent'] ?? null)->toBeNull()
        ->and($quote['total'])->toBe($totalBefore)
        ->and(round($quote['total'], 2))->toBe(15000.0)
        ->and($quote['version'])->toBe(3);
});

it('يسجّل في السجلّ أرقام الإصدار كما وصلت العميل لا كما عُدّلت بعده', function () {
    Mail::fake();
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    // الإصدار 1 يصل العميل بإجمالي 22,800
    amountsEditor($sr, 20000, 14)->call('issueQuote', true);
    expect(QuoteController::quoteOf($sr->fresh())['total'])->toBe(22800.0);

    // ثم يُعدَّل السعر ويُحفظ دون إرسال: لا يزيد رقم الإصدار ولا يُسجَّل شيء بعد،
    // لكن المخزَّن صار يحمل أرقاماً لم يرها العميل تحت الإصدار 1 نفسه
    Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.items.0.price', 16000)
        ->call('issueQuote', false);

    $afterEdit = QuoteController::quoteOf($sr->fresh());
    expect($afterEdit['version'])->toBe(1)
        ->and($afterEdit['total'])->toBe(18240.0)
        ->and($afterEdit['history'])->toBeEmpty();

    // إصدار ثانٍ: السجلّ يحمل أرقام الإصدار 1 كما أُرسلت (22,800) لا كما عُدّلت (18,240)
    Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->set('draft.discount_percent', 10)
        ->call('issueQuote', true);

    $quote = QuoteController::quoteOf($sr->fresh());
    expect($quote['version'])->toBe(2)
        ->and($quote['history'])->toHaveCount(1)
        ->and($quote['history'][0])->toMatchArray(['version' => 1, 'subtotal' => 20000.0, 'total' => 22800.0])
        ->and($quote['total'])->toBe(16416.0);

    // العروض الصادرة قبل هذه اللقطة تُقرأ من المخزَّن كما كان (توافق خلفي)
    $legacy = amountsRequest();
    $legacy->update(['payload' => ['_quote' => [
        'items' => [['name' => 'بند', 'qty' => 1, 'price' => 10000]],
        'discount_percent' => 0, 'vat_percent' => 0, 'currency' => 'ج.م',
        'issued_at' => now()->subDay()->toIso8601String(), 'version' => 1,
    ]]]);

    Livewire::test(QuoteRequests::class)->call('openQuote', $legacy->id)->call('issueQuote', true);

    expect(QuoteController::quoteOf($legacy->fresh())['history'][0])
        ->toMatchArray(['version' => 1, 'subtotal' => 10000.0, 'total' => 10000.0]);
});

it('يطبع نسبة الخصم الكسرية بدقّتها في عرض السعر فتطابق قيمتها المعروضة', function () {
    Mail::fake();
    $this->actingAs(amountsAdmin());
    $sr = amountsRequest();

    amountsEditor($sr, 18441.17, 14)
        ->call('setDiscountAmount', '5283.28')
        ->call('issueQuote', false);

    $shown = rtrim(rtrim(number_format(QuoteController::quoteOf($sr->fresh())['discount_percent'], 4), '0'), '.');

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('الخصم ('.$shown.'%)')
        ->assertSee('5,283.28');

    // النسبة المطبوعة تُعيد القيمة المطبوعة عند ضربها — وبمنزلتين عشريتين فقط كانت تعطي 5283.40
    expect($shown)->toBe('28.6494')
        ->and(round(18441.17 * (float) $shown / 100, 2))->toBe(5283.28)
        ->and(round(18441.17 * 28.65 / 100, 2))->not->toBe(5283.28);

    // النِّسب الصحيحة تبقى بلا كسور معروضة
    amountsEditor($sr, 20000)->call('setDiscountAmount', '5000')->call('issueQuote', false);

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('الخصم (25%)')
        ->assertDontSee('الخصم (25.0000%)');
});
