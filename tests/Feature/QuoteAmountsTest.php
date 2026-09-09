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
