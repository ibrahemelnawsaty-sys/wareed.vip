<?php

use App\Filament\Pages\Contracts as ContractsPage;
use App\Filament\Pages\EmailTemplates;
use App\Filament\Pages\QuoteRequests;
use App\Http\Controllers\QuoteController;
use App\Mail\QuoteProposalIssued;
use App\Mail\StageMessage;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\Contracts;
use App\Support\MailTemplates;
use App\Support\ServiceFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function flowAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

/** خدمة بحقول نموذجها كما في بيانات الإنتاج. */
function flowService(string $key): Service
{
    $definitions = [
        'training' => [
            'slug' => 'training',
            'name' => ['ar' => 'التدريب التقني', 'en' => 'Tech Training'],
            'form_fields' => [
                ['name' => 'track', 'label' => 'المسار التدريبي', 'type' => 'select', 'options' => ['الذكاء الاصطناعي', 'Backend', 'Frontend']],
                ['name' => 'trainees_count', 'label' => 'عدد المتدربين', 'type' => 'text'],
            ],
        ],
        'tech_solution' => [
            'slug' => 'solutions',
            'name' => ['ar' => 'الحلول التقنية', 'en' => 'Tech Solutions'],
            'form_fields' => [
                ['name' => 'entity_type', 'label' => 'نوع الجهة', 'type' => 'select', 'options' => ['جهة حكومية', 'شركة خاصة']],
                ['name' => 'solution_type', 'label' => 'نوع الحل المطلوب', 'type' => 'select', 'options' => ['نظام إداري', 'تطبيق موبايل']],
            ],
        ],
    ];

    return Service::updateOrCreate(['key' => $key], $definitions[$key] + ['is_active' => true]);
}

/** قرارات اعتماد لكل بنود العقد المفتوحة. */
function flowApproveAll(array $contract): array
{
    $out = [];
    foreach ($contract['clauses'] as $clause) {
        if (! $clause['locked']) {
            $out[$clause['id']] = ['decision' => 'approved'];
        }
    }

    return $out;
}

it('تحمل كل خدمة المراحل الثماني نفسها بمفاتيحها وتختلف في التسميات فقط', function () {
    $keys = array_keys(QuoteController::STAGES);

    expect(ServiceFlow::stages('ecommerce'))->toBe(QuoteController::STAGES)
        ->and(array_keys(ServiceFlow::stages('training')))->toBe($keys)
        ->and(array_keys(ServiceFlow::stages('tech_solution')))->toBe($keys)
        ->and(ServiceFlow::stages('training')['awaiting_meeting']['label'])->toBe('تحديد موعد المكالمة')
        ->and(ServiceFlow::stages('training')['meeting_scheduled']['label'])->toBe('المكالمة التعريفية')
        ->and(ServiceFlow::stages('training')['awaiting_requirements']['label'])->toBe('بيانات المتدربين والجدولة')
        ->and(ServiceFlow::stages('training')['in_progress']['label'])->toBe('التدريب جارٍ')
        ->and(ServiceFlow::stages('training')['in_progress']['countdown'])->toBeTrue()
        ->and(ServiceFlow::stages('tech_solution')['in_progress']['label'])->toBe('تنفيذ المشروع')
        ->and(ServiceFlow::stages('tech_solution')['awaiting_requirements']['label'])->toBe('رفع متطلبات المشروع')
        ->and(ServiceFlow::stages('tech_solution')['delivered']['client'])->toContain('تدريب فريقكم')
        ->and(ServiceFlow::stages('tech_solution')['awaiting_contract'])->toBe(QuoteController::STAGES['awaiting_contract']);

    // الأنواع غير المعروفة (والطلبات القديمة) تُعامل كمتاجر إلكترونية
    expect(ServiceFlow::type('general'))->toBe('ecommerce')
        ->and(ServiceFlow::type(null))->toBe('ecommerce')
        ->and(ServiceFlow::profile('training')['label'])->toBe('البرامج التدريبية')
        ->and(ServiceFlow::profile('tech_solution')['document_title'])->toBe('طلب حل تقني')
        ->and(ServiceFlow::profile('ecommerce')['company_label'])->toBe('اسم المتجر');

    $store = new ServiceRequest(['service_type' => 'ecommerce', 'company' => 'حواديت']);
    $storeNamed = new ServiceRequest(['service_type' => 'ecommerce', 'company' => 'متجر حواديت']);
    $solution = new ServiceRequest(['service_type' => 'tech_solution', 'company' => 'شركة النور']);
    $training = new ServiceRequest(['service_type' => 'training', 'company' => '']);

    expect(ServiceFlow::project($store))->toBe('متجر حواديت')
        ->and(ServiceFlow::project($storeNamed))->toBe('متجر حواديت')
        ->and(ServiceFlow::project($solution))->toBe('مشروع شركة النور')
        ->and(ServiceFlow::project($training))->toBe('برنامجك التدريبي');
});

it('يمرّ طلب البرنامج التدريبي بالمسار كاملاً بمفرداته: المكالمة والعرض والعقد وبيانات المتدربين', function () {
    Mail::fake();
    $this->actingAs(flowAdmin());
    flowService('training');

    $this->post(route('services.submit', 'training'), [
        'name' => 'د. سارة محمود', 'phone' => '01000000000', 'email' => 'sara@example.com',
        'company' => 'جامعة النيل', 'budget' => 'حتى 100 ألف', 'message' => 'نريد تدريب فريق التطوير.',
        'extra_track' => 'Backend', 'extra_trainees_count' => '12',
    ])->assertRedirect();

    $sr = ServiceRequest::sole();
    expect($sr->service_type)->toBe('training')
        ->and($sr->source)->toBe('service_training')
        ->and(ServiceFlow::answers($sr))->toBe(['المسار التدريبي' => 'Backend', 'عدد المتدربين' => '12']);

    // مستند الطلب بمفردات الخدمة وحقول نموذجها
    $this->get(URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('طلب برنامج تدريبي')
        ->assertSee('TRAINING PROGRAM REQUEST')
        ->assertSee('البرامج التدريبية')
        ->assertSee('المسار التدريبي')
        ->assertSee('Backend')
        ->assertSee('عدد المتدربين')
        ->assertSee('نريد تدريب فريق التطوير.')
        ->assertSee('الجهة')
        ->assertDontSee('اسم المتجر')
        ->assertDontSee('طلب متجر إلكتروني');

    // لوحة المتابعة: مرشّح الخدمة وتسميات مراحلها
    $page = Livewire::test(QuoteRequests::class)->call('setFilter', 'training');
    $rows = $page->get('requests');
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['service_label'])->toBe('البرامج التدريبية')
        ->and($rows[0]['company_label'])->toBe('الجهة')
        ->and($rows[0]['stages']['awaiting_meeting']['label'])->toBe('تحديد موعد المكالمة')
        ->and($rows[0]['payload'])->toHaveKey('المسار التدريبي');
    expect(Livewire::test(QuoteRequests::class)->call('setFilter', 'ecommerce')->get('requests'))->toBeEmpty();
    $this->get('/admin/quote-requests')->assertSuccessful()->assertSee('تحديد موعد المكالمة')->assertSee('البرامج التدريبية');

    // المكالمة التعريفية ببريد صياغة التدريب
    Livewire::test(QuoteRequests::class)
        ->set('flowInput.'.$sr->id.'.meeting_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('setMeeting', $sr->id)
        ->call('meetingDone', $sr->id);
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'تأكيد موعد المكالمة') && $m->hasTo('sara@example.com'));
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->bodyText, 'المنهج التدريبي وعرض السعر'));
    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('quote_due');

    // عرض السعر: بند مقترح من الخدمة وإجاباتها، وبريد ومستند بمفردات التدريب
    Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.items.0.name', 'البرامج التدريبية — Backend، 12')
        ->assertSet('draft.payments.1.label', 'دفعة عند التسليم')
        ->set('draft.items.0.name', 'برنامج Backend المكثّف')
        ->set('draft.items.0.price', 30000)
        ->set('draft.vat_percent', 0)
        ->call('issueQuote', true);

    Mail::assertSent(QuoteProposalIssued::class, function ($mail) {
        return str_contains($mail->envelope()->subject, 'عرض سعر برنامجك التدريبي')
            && str_contains($mail->render(), 'عرض سعر برنامجك التدريبي')
            && str_contains($mail->render(), 'المنهج وعرض السعر المخصّص للبرنامج التدريبي لـجامعة النيل');
    });

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('البرامج التدريبية')
        ->assertSee('الجهة')
        ->assertDontSee('اسم المتجر')
        ->assertDontSee('المتاجر الإلكترونية');

    // اعتماد العرض → مرحلة العقد بصياغة التدريب
    $this->post(URL::signedRoute('quote.decision.signed', ['serviceRequest' => $sr->id]), ['choice' => 'approved'])->assertRedirect();
    $sr = $sr->fresh();
    expect(QuoteController::flowOf($sr)['stage'])->toBe('awaiting_contract');
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'عقد برنامجك التدريبي قيد التجهيز'));

    $contract = Contracts::of($sr);
    expect($contract['clauses'][1]['title'])->toBe('المادة الثانية: تعريف البرنامج ونطاقه')
        ->and($contract['clauses'][1]['body'])->toContain('برنامج Backend المكثّف')
        ->and($contract['clauses'][2]['body'])->toContain('شهادات إتمام')
        ->and($contract['clauses'][7]['title'])->toContain('المواد التدريبية')
        ->and($contract['clauses'][5]['body'])->toContain('تقديم البرنامج');

    Contracts::send($sr);
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'مسوّدة عقد برنامجك التدريبي للمراجعة'));

    $this->get(URL::signedRoute('quote.contract.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('عقد برنامج تدريبي')
        ->assertSee('TRAINING AGREEMENT')
        ->assertSee('جامعة النيل')
        ->assertDontSee('اسم المتجر');

    // اعتماد العقد → بيانات المتدربين والجدولة
    $this->post(URL::signedRoute('quote.contract.decision.signed', ['serviceRequest' => $sr->id]), [
        'clauses' => flowApproveAll(Contracts::of($sr->fresh())),
    ])->assertSessionHas('contract_saved', 'approved');

    $sr = $sr->fresh();
    expect(QuoteController::flowOf($sr)['stage'])->toBe('awaiting_requirements');

    $this->get(URL::signedRoute('quote.proposal.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('بيانات المتدربين والجدولة')
        ->assertSee('قائمة المتدربين')
        ->assertDontSee('هويتك البصرية');

    expect(MailTemplates::sendStage($sr, 'awaiting_requirements'))->toBeTrue();
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'بيانات المتدربين وموعد الانطلاق'));

    // بدء التنفيذ والتسليم بتسميات التدريب في اللوحة
    Livewire::test(QuoteRequests::class)
        ->set('flowInput.'.$sr->id.'.due_at', now()->addDays(30)->format('Y-m-d'))
        ->call('startExecution', $sr->id)
        ->call('markDelivered', $sr->id);
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'انطلق برنامجك التدريبي'));
    Mail::assertSent(StageMessage::class, fn ($m) => str_contains($m->subjectLine, 'اكتمل برنامجك التدريبي'));
    expect(QuoteController::flowOf($sr->fresh())['stage'])->toBe('delivered');
});

it('يعرض طلب الحل التقني بمفرداته ويُدرجه في صفحتي المتابعة والعقود', function () {
    Mail::fake();
    $this->actingAs(flowAdmin());
    $service = flowService('tech_solution');

    $sr = ServiceRequest::create([
        'service_id' => $service->id, 'service_type' => 'tech_solution', 'source' => 'service_tech_solution',
        'name' => 'م. خالد', 'phone' => '01111111111', 'email' => 'khaled@example.com', 'company' => 'شركة النور',
        'status' => 'new', 'payload' => ['entity_type' => 'شركة خاصة', 'solution_type' => 'نظام إداري'],
    ]);

    $this->get(URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]))
        ->assertSuccessful()
        ->assertSee('طلب حل تقني')
        ->assertSee('TECH SOLUTION REQUEST')
        ->assertSee('نوع الحل المطلوب')
        ->assertSee('نظام إداري')
        ->assertSee('الحلول التقنية');

    expect(MailTemplates::body('awaiting_requirements', 'tech_solution'))->toContain('صلاحيات الوصول')
        ->and(MailTemplates::subject('proposal_sent', 'tech_solution'))->toContain('مشروعك التقني')
        ->and(MailTemplates::body('contract_resent', 'tech_solution'))->toBe(MailTemplates::TEMPLATES['contract_resent']['body'])
        ->and(MailTemplates::variables($sr)['{الجهة}'])->toBe('شركة النور')
        ->and(MailTemplates::variables($sr)['{الخدمة}'])->toBe('الحلول التقنية')
        ->and(MailTemplates::variables()['{الجهة}'])->not->toBe('');

    Livewire::test(QuoteRequests::class)
        ->call('openQuote', $sr->id)
        ->assertSet('draft.items.0.name', 'الحلول التقنية — شركة خاصة، نظام إداري');

    // بعد اعتماد العرض يظهر في صفحة العقود بمفردات الخدمة
    $sr->update(['payload' => array_merge((array) $sr->payload, [
        '_quote' => ['items' => [['name' => 'نظام إداري', 'qty' => 1, 'price' => 50000]], 'issued_at' => now()->toIso8601String()],
        '_decision' => ['choice' => 'approved', 'note' => '', 'at' => now()->toIso8601String()],
        '_flow' => ['stage' => 'awaiting_contract'],
    ])]);

    $rows = Livewire::test(ContractsPage::class)->get('rows');
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['service_label'])->toBe('الحلول التقنية')
        ->and($rows[0]['company_label'])->toBe('الجهة');

    $contract = Contracts::createDraft($sr->fresh());
    expect($contract['clauses'][1]['title'])->toBe('المادة الثانية: تعريف المشروع ونطاقه')
        ->and($contract['clauses'][7]['title'])->toContain('الملكية الفكرية وتسليم المصادر');

    $this->get('/admin/contracts')->assertSuccessful()->assertSee('الحلول التقنية');
});

it('تحرّر قوالب البريد لكل خدمة على حدة دون المساس بقوالب المتاجر', function () {
    $this->actingAs(flowAdmin());

    $page = Livewire::test(EmailTemplates::class)
        ->assertSet('type', 'ecommerce')
        ->call('selectType', 'training')
        ->assertSet('type', 'training')
        ->assertSet('subject', MailTemplates::VARIANTS['training']['received']['subject'])
        ->assertSet('body', MailTemplates::VARIANTS['training']['received']['body']);

    $page->call('selectType', 'غير-موجودة')->assertSet('type', 'training');

    $page->call('select', 'awaiting_meeting')
        ->assertSet('subject', 'موعد مكالمتنا التعريفية — {الرقم_المرجعي}')
        ->set('subject', 'موعد مكالمة {الجهة}')
        ->call('save');

    expect(MailTemplates::subject('awaiting_meeting', 'training'))->toBe('موعد مكالمة {الجهة}')
        ->and(MailTemplates::isCustomised('awaiting_meeting', 'training'))->toBeTrue()
        ->and(MailTemplates::isCustomised('awaiting_meeting', 'ecommerce'))->toBeFalse()
        ->and(MailTemplates::subject('awaiting_meeting'))->toBe(MailTemplates::TEMPLATES['awaiting_meeting']['subject'])
        ->and(MailTemplates::subject('awaiting_meeting', 'tech_solution'))->toBe(MailTemplates::VARIANTS['tech_solution']['awaiting_meeting']['subject']);

    $page->call('resetTemplate')->assertSet('subject', 'موعد مكالمتنا التعريفية — {الرقم_المرجعي}');
    expect(MailTemplates::isCustomised('awaiting_meeting', 'training'))->toBeFalse();

    $this->get('/admin/email-templates')->assertSuccessful()->assertSee('البرامج التدريبية')->assertSee('الحلول التقنية');
});
