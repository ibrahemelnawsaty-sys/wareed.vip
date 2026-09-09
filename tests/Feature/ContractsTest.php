<?php

use App\Filament\Pages\Contracts as ContractsPage;
use App\Http\Controllers\QuoteController;
use App\Mail\StageMessage;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\User;
use App\Support\ContractNumber;
use App\Support\Contracts;
use App\Support\MailTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function contractAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

/** طلب متجر اعتمد صاحبه عرض السعر ووصل مرحلة العقد. */
function contractRequest(array $attributes = [], array $payload = []): ServiceRequest
{
    return ServiceRequest::create(array_merge([
        'service_type' => 'ecommerce',
        'name' => 'أ. هاجر سلامة',
        'company' => 'متجر حواديت',
        'phone' => '00201016031031',
        'email' => 'hagersalma89@gmail.com',
        'status' => 'won',
        'source' => 'quote_link:hajar-salama',
        'payload' => array_merge([
            '_quote' => [
                'items' => [['name' => 'تجهيز المتجر الإلكتروني', 'qty' => 1, 'price' => 10000]],
                'vat_percent' => 0,
                'payments' => [['label' => 'دفعة مقدّمة', 'percent' => 50], ['label' => 'دفعة التسليم', 'percent' => 50]],
                'issued_at' => now()->toIso8601String(),
            ],
            '_decision' => ['choice' => 'approved', 'note' => '', 'at' => now()->toIso8601String()],
            '_flow' => ['stage' => 'awaiting_contract', 'approved_at' => now()->toIso8601String()],
        ], $payload),
    ], $attributes));
}

/** قرارات العميل على كل البنود المفتوحة، مع تجاوزات لبنود بعينها. */
function contractDecisions(array $contract, string $decision = 'approved', array $overrides = []): array
{
    $out = [];

    foreach ($contract['clauses'] as $clause) {
        if ($clause['locked']) {
            continue;
        }

        $out[$clause['id']] = $overrides[$clause['id']] ?? [
            'decision' => $decision,
            'note' => $decision === 'approved' ? '' : 'ملاحظة العميل',
        ];
    }

    return $out;
}

it('يولّد أرقام عقود تسلسلية بصيغة WRD-CTR لا تُعاد', function () {
    $first = ContractNumber::next();
    $second = ContractNumber::next();

    expect($first)->toMatch('/^WRD-CTR-\d{4}-\d{2}-\d{2}-00001$/')
        ->and($second)->toEndWith('-00002')
        ->and(ContractNumber::next())->toEndWith('-00003')
        // العدّاد رقم داخلي صرف خارج طبقة ترجمة الإعدادات، فلا يظهر عبر Setting::get
        ->and(Setting::get('contract_sequence'))->toBeNull();
});

it('اعتماد العميل للعرض ينقل الطلب لمرحلة العقد ويُنشئ مسوّدته تلقائياً', function () {
    Mail::fake();

    $sr = contractRequest(['status' => 'proposal'], ['_decision' => null, '_flow' => ['stage' => 'awaiting_approval']]);

    $this->post(route('quote.decision', 'hajar-salama'), ['choice' => 'approved'])->assertRedirect();

    $sr = $sr->fresh();
    $contract = Contracts::of($sr);

    expect(QuoteController::flowOf($sr)['stage'])->toBe('awaiting_contract')
        ->and($sr->status)->toBe('won')
        ->and($contract)->not->toBeNull()
        ->and($contract['status'])->toBe('draft')
        ->and($contract['round'])->toBe(0)
        ->and($contract['count'])->toBe(13)
        ->and($contract['number'])->toMatch('/^WRD-CTR-\d{4}-\d{2}-\d{2}-00001$/')
        ->and($contract['clauses'][0]['body'])->toContain('أ. هاجر سلامة')
        ->and($contract['clauses'][5]['body'])->toContain('10,000 ج.م');

    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'عقد مشروعك قيد التجهيز') && $m->hasTo('hagersalma89@gmail.com'));
    Mail::assertNotSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'رفع متطلبات مشروعك'));
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->bodyText, 'أُنشئت مسوّدة العقد رقم '.$contract['number']));
});

it('إعادة تسجيل الاعتماد لا تُعيد الطلب للوراء ولا تستهلك رقم عقد جديد', function () {
    Mail::fake();

    $sr = contractRequest([], ['_flow' => ['stage' => 'awaiting_requirements']]);
    $number = Contracts::createDraft($sr)['number'];

    $this->post(route('quote.decision', 'hajar-salama'), ['choice' => 'approved'])->assertRedirect();

    $sr = $sr->fresh();

    expect(QuoteController::flowOf($sr)['stage'])->toBe('awaiting_requirements')
        ->and(Contracts::of($sr)['number'])->toBe($number)
        ->and(ContractNumber::next())->toEndWith('-00002');
});

it('المسوّدة لا تُعرض للعميل قبل إرسالها ثم تظهر ببنودها وقرارها', function () {
    Mail::fake();

    $sr = contractRequest();
    Contracts::createDraft($sr);

    $this->get(route('quote.contract', 'hajar-salama'))->assertNotFound();

    expect(Contracts::send($sr))->toBeTrue();

    $contract = Contracts::of($sr->fresh());

    $this->get(route('quote.contract', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee($contract['number'])
        ->assertSee('قرارك على مسوّدة العقد')
        ->assertSee('المادة الأولى: تعريف الطرفين')
        ->assertSee('اعتماد مسوّدة العقد')
        ->assertSee('إرسال ملاحظات العقد')
        ->assertSee(Contracts::DEFAULT_SIGNER['name'])
        ->assertSee('مسوّدة للمراجعة — الجولة 1')
        // زرّا الأسفل كبسوليان بـ inline-flex، فالسمة hidden تحتاج قاعدة صريحة وإلا ظهر الزرّان معاً
        ->assertSee('[hidden] { display: none !important; }', false);

    expect($contract['status'])->toBe('sent')
        ->and($contract['round'])->toBe(1)
        ->and($contract['sent_at'])->not->toBeNull()
        ->and($contract['rounds'])->toHaveCount(1);

    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'مسوّدة عقد مشروعك للمراجعة')
        && str_contains($m->subjectLine, $contract['number'])
        && str_contains((string) $m->link, '/quote/hajar-salama/contract')
        && $m->hasTo('hagersalma89@gmail.com'));
});

it('يعرض العقد للنموذج العام عبر رابط موقّع فقط', function () {
    Mail::fake();

    $sr = contractRequest(['source' => 'quote_form']);
    Contracts::createDraft($sr);
    Contracts::send($sr);

    $this->get('/quote/contract/'.$sr->id)->assertForbidden();

    $this->get(URL::signedRoute('quote.contract.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee(Contracts::of($sr->fresh())['number']);

    expect(Contracts::reviewUrl($sr))->toContain('/quote/contract/'.$sr->id.'?')
        ->and(Contracts::decisionUrl($sr))->toContain('signature=');
});

it('إرسال العقد قبل قرار العميل على العرض ينقل المسار لمرحلة العقد', function () {
    Mail::fake();

    $sr = contractRequest(['status' => 'proposal'], ['_decision' => null, '_flow' => ['stage' => 'awaiting_approval']]);
    Contracts::createDraft($sr);
    Contracts::send($sr);

    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('awaiting_contract');
});

it('يرفض القرارات الناقصة أو بلا ملاحظة للتعديل والحذف ولا يقبلها قبل الإرسال', function () {
    Mail::fake();

    $sr = contractRequest();
    $contract = Contracts::createDraft($sr);

    expect(Contracts::applyDecisions($sr, contractDecisions($contract)))
        ->toMatchArray(['ok' => false, 'error' => 'لم يُرسل هذا العقد للمراجعة بعد.']);

    Contracts::send($sr);
    $contract = Contracts::of($sr->fresh());
    $first = $contract['clauses'][0]['id'];

    $partial = contractDecisions($contract);
    unset($partial[$first]);

    $this->from(route('quote.contract', 'hajar-salama'))
        ->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => $partial])
        ->assertRedirect(route('quote.contract', 'hajar-salama'))
        ->assertSessionHasErrors('contract');

    $noNote = contractDecisions($contract, 'approved', [$first => ['decision' => 'edited', 'note' => '  ']]);

    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => $noNote])
        ->assertSessionHasErrors('contract');

    expect(Contracts::of($sr->fresh())['status'])->toBe('sent')
        ->and(Contracts::of($sr->fresh())['decided_count'])->toBe(0);

    Mail::assertNotSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'ملاحظات العميل'));
});

it('اعتماد كل البنود يعتمد العقد وينقل الطلب لرفع المتطلبات ويرسل بريد الشكر وإشعار الفريق', function () {
    Mail::fake();

    $sr = contractRequest();
    Contracts::createDraft($sr);
    Contracts::send($sr);
    $contract = Contracts::of($sr->fresh());

    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => contractDecisions($contract)])
        ->assertRedirect()
        ->assertSessionHas('contract_saved', 'approved');

    $sr = $sr->fresh();
    $contract = Contracts::of($sr);
    $flow = QuoteController::flowOf($sr);

    expect($contract['is_approved'])->toBeTrue()
        ->and($contract['all_approved'])->toBeTrue()
        ->and($contract['approved_at'])->not->toBeNull()
        ->and($contract['rounds'][0]['outcome'])->toBe('approved')
        ->and($flow['stage'])->toBe('awaiting_requirements');

    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'شكراً لاعتمادك العقد')
        && str_contains($m->bodyText, 'نسخة من العقد موقّعة من الشركة')
        && $m->hasTo('hagersalma89@gmail.com'));
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'اعتمد العميل العقد '.$contract['number'])
        && $m->hasTo('info@wareed.vip'));

    // العقد المعتمد لا يقبل قرارات جديدة، وتظهر صفحته معتمدة مع دعوة رفع المتطلبات
    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => contractDecisions($contract)])
        ->assertSessionHasErrors('contract');

    $this->get(route('quote.contract', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee('نسخة معتمدة من العميل')
        ->assertSee('رفع متطلبات المشروع')
        ->assertDontSee('إرسال ملاحظات العقد');

    $this->get(route('quote.proposal', 'hajar-salama'))->assertSuccessful()->assertSee('id="requirements"', false);
});

it('إرسال الملاحظات يقفل البنود المعتمدة ويعيد فتح غيرها فقط في الجولة التالية', function () {
    Mail::fake();

    $sr = contractRequest();
    Contracts::createDraft($sr);
    Contracts::send($sr);
    $contract = Contracts::of($sr->fresh());
    [$a, $b, $c] = [$contract['clauses'][0]['id'], $contract['clauses'][1]['id'], $contract['clauses'][2]['id']];

    $decisions = contractDecisions($contract, 'approved', [
        $b => ['decision' => 'edited', 'note' => 'أرجو تمديد الضمان إلى ستين يوماً'],
        $c => ['decision' => 'deleted', 'note' => 'لا ينطبق على مشروعي'],
    ]);

    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => $decisions])
        ->assertSessionHas('contract_saved', 'feedback');

    $contract = Contracts::of($sr->fresh());

    expect($contract['status'])->toBe('feedback')
        ->and($contract['objections_count'])->toBe(2)
        ->and($contract['approved_count'])->toBe($contract['count'] - 2)
        ->and($contract['feedback_at'])->not->toBeNull()
        ->and($contract['rounds'][0]['outcome'])->toBe('feedback')
        ->and(QuoteController::flowOf($sr->fresh())['stage'])->toBe('awaiting_contract');

    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'ملاحظات العميل على العقد')
        && str_contains($m->bodyText, 'أرجو تمديد الضمان إلى ستين يوماً')
        && $m->hasTo('info@wareed.vip'));

    // صفحة العميل بعد الملاحظات للقراءة فقط، ولا تقبل قرارات جديدة حتى إعادة الإرسال
    $this->get(route('quote.contract', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee('ملاحظاتك قيد المراجعة')
        ->assertDontSee('اعتماد مسوّدة العقد');

    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => $decisions])
        ->assertSessionHasErrors('contract');

    // إعادة الإرسال: جولة ثانية، المعتمد مقفل، والمعدَّل والمحذوف بلا قرار
    expect(Contracts::send($sr->fresh()))->toBeTrue();
    $contract = Contracts::of($sr->fresh());
    $byId = collect($contract['clauses'])->keyBy('id');

    expect($contract['round'])->toBe(2)
        ->and($contract['status'])->toBe('sent')
        ->and($contract['rounds'])->toHaveCount(2)
        ->and($byId[$a]['locked'])->toBeTrue()
        ->and($byId[$b]['decision'])->toBeNull()
        ->and($byId[$b]['note'])->toBe('')
        ->and($byId[$c]['decision'])->toBeNull()
        ->and($contract['pending_count'])->toBe(2);

    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'النسخة المعدَّلة من عقد مشروعك'));

    $this->get(route('quote.contract', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee('اعتمدته مسبقاً')
        ->assertSee('نسخة معدَّلة');

    // محاولة تغيير قرار بند مقفل تُتجاهل، ويكفي قرار البندين المفتوحين لاعتماد العقد
    $this->post(route('quote.contract.decision', 'hajar-salama'), ['clauses' => [
        $a => ['decision' => 'deleted', 'note' => 'تغيير رأي'],
        $b => ['decision' => 'approved'],
        $c => ['decision' => 'approved'],
    ]])->assertSessionHas('contract_saved', 'approved');

    $contract = Contracts::of($sr->fresh());
    $byId = collect($contract['clauses'])->keyBy('id');

    expect($contract['is_approved'])->toBeTrue()
        ->and($byId[$a]['decision'])->toBe('approved')
        ->and($byId[$a]['note'])->toBe('')
        ->and($contract['rounds'][1]['outcome'])->toBe('approved');
});

it('تعديل نص بند اعتمده العميل يُسقط اعتماده وغير المعدَّل يبقى معتمداً', function () {
    Mail::fake();

    $sr = contractRequest();
    Contracts::createDraft($sr);
    Contracts::send($sr);
    $contract = Contracts::of($sr->fresh());
    $edited = $contract['clauses'][3]['id'];

    Contracts::applyDecisions($sr->fresh(), contractDecisions($contract, 'approved', [
        $edited => ['decision' => 'edited', 'note' => 'تعديل'],
    ]));

    $contract = Contracts::of($sr->fresh());
    $clauses = $contract['clauses'];
    $clauses[0]['title'] = 'المادة الأولى: الطرفان (معدَّلة)';
    unset($clauses[1]);
    $clauses[] = ['id' => '', 'section' => 'أحكام عامة', 'title' => 'بند جديد', 'body' => 'نص البند الجديد'];

    $saved = Contracts::saveClauses($sr->fresh(), array_values($clauses));
    $byId = collect($saved['clauses'])->keyBy('id');

    expect($saved['count'])->toBe($contract['count'])
        ->and($byId[$contract['clauses'][0]['id']]['decision'])->toBeNull()
        ->and($byId->has($contract['clauses'][1]['id']))->toBeFalse()
        ->and($byId[$contract['clauses'][2]['id']]['locked'])->toBeTrue()
        ->and($byId[$edited]['decision'])->toBe('edited')
        ->and($saved['clauses'][$saved['count'] - 1])->toMatchArray(['title' => 'بند جديد', 'decision' => null, 'locked' => false])
        ->and($saved['clauses'][$saved['count'] - 1]['id'])->toHaveLength(36);
});

it('الطلبات القديمة بلا عقد تبقى تُقرأ وتُعرض ولا يُطلب منها عقد', function () {
    Mail::fake();

    $sr = contractRequest([], ['_flow' => ['stage' => 'awaiting_requirements', 'approved_at' => now()->toIso8601String()]]);

    expect(Contracts::of($sr))->toBeNull()
        ->and(QuoteController::flowOf($sr)['index'])->toBe(5)
        ->and(QuoteController::stageReached('awaiting_requirements', 'awaiting_contract'))->toBeTrue()
        ->and(QuoteController::stageReached('awaiting_contract', 'awaiting_requirements'))->toBeFalse()
        ->and(QuoteController::stageReached('غير-موجودة', 'awaiting_contract'))->toBeFalse();

    $this->get(route('quote.invite', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee('ارفع متطلبات مشروعك')
        ->assertSee('توقيع العقد')
        ->assertDontSee('مراجعة العقد الآن');

    $this->get(route('quote.contract', 'hajar-salama'))->assertNotFound();
    $this->get(route('quote.proposal', 'hajar-salama'))->assertSuccessful()->assertSee('id="requirements"', false);
});

it('لا يعرض رفع المتطلبات على عرض السعر قبل اعتماد العقد ويعرض حالته في صفحة الحالة', function () {
    Mail::fake();

    $sr = contractRequest();
    Contracts::createDraft($sr);

    $this->get(route('quote.proposal', 'hajar-salama'))
        ->assertSuccessful()
        ->assertDontSee('id="requirements"', false)
        ->assertSee('يجهّز فريق وريد مسوّدة العقد');

    // الرفع المباشر مرفوض أيضاً ما دام العقد بانتظار الاعتماد
    $this->post(route('quote.requirements', 'hajar-salama'), [
        'files' => [['file' => UploadedFile::fake()->create('logo.png', 10), 'desc' => '']],
    ])->assertForbidden();

    Contracts::send($sr);

    $this->get(route('quote.proposal', 'hajar-salama'))->assertSuccessful()->assertSee('مراجعة العقد الآن');

    $this->get(route('quote.invite', 'hajar-salama'))
        ->assertSuccessful()
        ->assertSee('اعتُمد عرض متجرك يا')
        ->assertSee('راجع بنود العقد واعتمدها')
        ->assertSee('مراجعة العقد الآن');
});

it('صفحة العقود في اللوحة تُنشئ المسوّدة وتحرّر البنود وترسلها وتحذفها', function () {
    Mail::fake();
    $this->actingAs(contractAdmin());

    $sr = contractRequest();

    $this->get('/admin/contracts')->assertSuccessful()->assertSee('إنشاء مسوّدة العقد');

    $page = Livewire::test(ContractsPage::class)
        ->call('openContract', $sr->id)
        ->assertSet('open', $sr->id);

    $contract = Contracts::of($sr->fresh());
    expect($contract)->not->toBeNull()->and($page->get('clauses'))->toHaveCount(13);

    $page->set('clauses.0.title', 'المادة الأولى: الطرفان')
        ->call('addClause')
        ->set('clauses.13.section', 'أحكام عامة')
        ->set('clauses.13.title', 'بند إضافي')
        ->set('clauses.13.body', 'نص البند الإضافي')
        ->call('moveClause', 13, -1)
        ->call('saveClauses', false)
        ->assertSet('open', $sr->id);

    $saved = Contracts::of($sr->fresh());
    expect($saved['count'])->toBe(14)
        ->and($saved['clauses'][0]['title'])->toBe('المادة الأولى: الطرفان')
        ->and($saved['clauses'][12]['title'])->toBe('بند إضافي')
        ->and($saved['status'])->toBe('draft');

    $page->call('removeClause', 12)->call('saveClauses', true)->assertSet('open', null);

    $sent = Contracts::of($sr->fresh());
    expect($sent['count'])->toBe(13)->and($sent['status'])->toBe('sent')->and($sent['round'])->toBe(1);
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'مسوّدة عقد مشروعك للمراجعة'));

    $this->get('/admin/contracts?open='.$sr->id)->assertSuccessful()->assertSee($sent['number'])->assertSee('إعادة الإرسال بعد التعديل');

    Livewire::test(ContractsPage::class)->call('deleteContract', $sr->id);
    expect(Contracts::of($sr->fresh()))->toBeNull();

    // بلا عرض سعر لا تُنشأ مسوّدة
    $noQuote = contractRequest(['source' => 'quote_form'], ['_quote' => null]);
    Livewire::test(ContractsPage::class)->call('openContract', $noQuote->id)->assertSet('open', null);
    expect(Contracts::of($noQuote->fresh()))->toBeNull();
});

it('لا يُحذف عقد اعتمده العميل ولا يُعاد إرساله', function () {
    Mail::fake();
    $this->actingAs(contractAdmin());

    $sr = contractRequest();
    Contracts::createDraft($sr);
    Contracts::send($sr);
    Contracts::applyDecisions($sr->fresh(), contractDecisions(Contracts::of($sr->fresh())));

    Livewire::test(ContractsPage::class)->call('deleteContract', $sr->id)->call('sendContract', $sr->id);

    $contract = Contracts::of($sr->fresh());
    expect($contract)->not->toBeNull()->and($contract['is_approved'])->toBeTrue()->and($contract['round'])->toBe(1);
});

it('قوالب بريد العقد موجودة ومتغيّر رقم العقد يُستبدل ويُعاين', function () {
    foreach (['awaiting_contract', 'contract_draft_sent', 'contract_resent', 'contract_approved'] as $key) {
        expect(MailTemplates::exists($key))->toBeTrue()
            ->and(MailTemplates::TEMPLATES[$key]['body'])->toContain('{العميل}');
    }

    expect(MailTemplates::TEMPLATES['contract_approved']['body'])
        ->toContain('سوف يتم إرسال نسخة من العقد موقّعة من الشركة وإعادة إرسالها لك للتوقيع')
        ->and(MailTemplates::VARIABLES)->toHaveKey('{رقم_العقد}')
        ->and(MailTemplates::variables()['{رقم_العقد}'])->toStartWith('WRD-CTR-');

    $sr = contractRequest();
    expect(MailTemplates::variables($sr)['{رقم_العقد}'])->toBe('—');

    $number = Contracts::createDraft($sr)['number'];
    expect(MailTemplates::variables($sr->fresh())['{رقم_العقد}'])->toBe($number)
        ->and(MailTemplates::render(MailTemplates::subject('contract_draft_sent'), MailTemplates::variables($sr->fresh())))
        ->toBe('مسوّدة عقد مشروعك للمراجعة — '.$number);
});

it('التوقيع عن الشركة قالب دائم من الإعدادات بقيمة افتراضية وختم اختياري', function () {
    $signature = Contracts::signature();

    expect($signature['name'])->toBe('م. أحمد سامي رجب محمد عبدالعزيز')
        ->and($signature['title'])->toBe('المدير التنفيذي لشركة وريد لتقنية المعلومات')
        ->and($signature['stamp_url'])->toBeNull();

    Setting::set('signer_name', 'م. اسم آخر');
    Setting::set('signer_title', 'المدير العام');
    Setting::set('contract_stamp', 'branding/لا-وجود-له.png');

    $signature = Contracts::signature();

    expect($signature['name'])->toBe('م. اسم آخر')
        ->and($signature['title'])->toBe('المدير العام')
        ->and($signature['stamp_url'])->toBeNull();
});
