<?php

namespace App\Filament\Pages;

use App\Http\Controllers\QuoteController;
use App\Mail\QuoteProposalIssued;
use App\Models\ServiceRequest;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * لوحة متابعة طلبات المتاجر الواردة من نموذج عرض السعر:
 * عرض الطلبات بأرقامها المرجعية، فتح مستند كل طلب، تغيير الحالة، وحذف طلب.
 * حذف طلب مرتبط برابط مخصّص يفتح الرابط من جديد لصاحبه.
 */
class QuoteRequests extends Page
{
    protected string $view = 'filament.pages.quote-requests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الموقع';

    protected static ?string $navigationLabel = 'متابعة طلبات المتاجر';

    protected static ?int $navigationSort = 1;

    public ?string $filter = 'all';

    /** الطلب المفتوح حالياً في محرّر عرض السعر (null = المحرّر مغلق). */
    public ?int $editingId = null;

    /** مسوّدة عرض السعر الجاري تحريرها. */
    public array $draft = [];

    /** مدخلات إدارة مراحل الطلب (تاريخ الاجتماع، موعد التسليم…) لكل طلب. */
    public array $flowInput = [];

    public function getTitle(): string
    {
        return 'متابعة طلبات المتاجر';
    }

    public static function getNavigationBadge(): ?string
    {
        $new = static::baseQuery()->where('status', 'new')->count();

        return $new > 0 ? (string) $new : null;
    }

    /** طلبات نموذج عرض السعر وحدها (الرابط المخصّص + النموذج العام). */
    protected static function baseQuery()
    {
        return ServiceRequest::query()
            ->where(fn ($q) => $q->where('source', 'quote_form')->orWhere('source', 'like', 'quote_link:%'));
    }

    /** @return array<int, array<string, mixed>> */
    public function getRequestsProperty(): array
    {
        return static::baseQuery()
            ->when($this->filter === 'new', fn ($q) => $q->where('status', 'new'))
            ->when($this->filter === 'invite', fn ($q) => $q->where('source', 'like', 'quote_link:%'))
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(function (ServiceRequest $sr) {
                $invite = str_starts_with((string) $sr->source, 'quote_link:')
                    ? substr((string) $sr->source, strlen('quote_link:'))
                    : null;

                return [
                    'id' => $sr->id,
                    'reference' => $sr->reference,
                    'name' => $sr->name,
                    'phone' => $sr->phone && $sr->phone !== '—' ? $sr->phone : null,
                    'email' => $sr->email,
                    'company' => $sr->company,
                    'budget' => $sr->budget,
                    'message' => $sr->message,
                    // المفاتيح الداخلية (مثل عرض السعر) تُعرض في قسمها الخاص لا ضمن إجابات العميل
                    'payload' => array_filter(
                        (array) $sr->payload,
                        fn ($key) => ! str_starts_with((string) $key, '_'),
                        ARRAY_FILTER_USE_KEY
                    ),
                    'status' => $sr->status,
                    'invite' => $invite,
                    'created' => $sr->created_at,
                    'deadline' => $sr->created_at ? QuoteController::deadlineFor($sr->created_at) : null,
                    'overdue' => $sr->status === 'new'
                        && $sr->created_at
                        && QuoteController::deadlineFor($sr->created_at)->isPast(),
                    'document' => $invite
                        ? route('quote.document', $invite)
                        : URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]),
                    'quote' => QuoteController::quoteOf($sr),
                    'proposal' => QuoteController::proposalUrl($sr),
                    'flow' => QuoteController::flowOf($sr),
                ];
            })
            ->all();
    }

    /** @return array<string, int> */
    public function getStatsProperty(): array
    {
        $all = static::baseQuery()->get(['status', 'created_at']);

        return [
            'total' => $all->count(),
            'new' => $all->where('status', 'new')->count(),
            'today' => $all->filter(fn ($r) => $r->created_at?->isToday())->count(),
            'overdue' => $all->filter(
                fn ($r) => $r->status === 'new'
                    && $r->created_at
                    && QuoteController::deadlineFor($r->created_at)->isPast()
            )->count(),
        ];
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /** فتح محرّر عرض السعر لطلب بعينه (يُحمّل العرض السابق إن وُجد). */
    public function openQuote(int $id): void
    {
        $sr = static::baseQuery()->whereKey($id)->firstOrFail();
        $saved = ((array) $sr->payload)['_quote'] ?? null;

        $this->editingId = $id;
        $this->draft = [
            'items' => ! empty($saved['items']) ? array_values($saved['items']) : $this->suggestedItems($sr),
            'discount' => (float) ($saved['discount'] ?? 0),
            'vat_percent' => (float) ($saved['vat_percent'] ?? QuoteController::DEFAULT_VAT_PERCENT),
            'currency' => (string) ($saved['currency'] ?? 'ج.م'),
            'valid_days' => (int) ($saved['valid_days'] ?? 30),
            'timeline' => (string) ($saved['timeline'] ?? ''),
            'notes' => (string) ($saved['notes'] ?? ''),
        ];
    }

    public function closeQuote(): void
    {
        $this->editingId = null;
        $this->draft = [];
    }

    public function addItem(): void
    {
        $this->draft['items'][] = ['name' => '', 'desc' => '', 'qty' => 1, 'price' => 0];
    }

    public function removeItem(int $index): void
    {
        unset($this->draft['items'][$index]);
        $this->draft['items'] = array_values($this->draft['items']);
    }

    /** بنود مقترحة مبنية على الخدمات التي اختارها العميل في النموذج. */
    private function suggestedItems(ServiceRequest $sr): array
    {
        $features = ((array) $sr->payload)['الخدمات المطلوبة'] ?? [];
        $features = is_array($features) ? $features : [$features];
        $features = array_values(array_filter($features, fn ($f) => $f !== 'أحتاج استشارة الفريق أولاً'));

        if (! $features) {
            $features = ['تجهيز المتجر الإلكتروني'];
        }

        return array_map(fn ($f) => ['name' => $f, 'desc' => '', 'qty' => 1, 'price' => 0], $features);
    }

    /** مجاميع المسوّدة لعرضها مباشرة أثناء التحرير. */
    public function getDraftTotalsProperty(): array
    {
        $subtotal = 0.0;

        foreach ($this->draft['items'] ?? [] as $item) {
            $subtotal += max(1, (int) ($item['qty'] ?? 1)) * max(0, (float) ($item['price'] ?? 0));
        }

        $discount = min(max(0, (float) ($this->draft['discount'] ?? 0)), $subtotal);
        $afterDiscount = $subtotal - $discount;
        $vat = round($afterDiscount * max(0, (float) ($this->draft['vat_percent'] ?? 0)) / 100, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat' => $vat,
            'total' => $afterDiscount + $vat,
            'currency' => $this->draft['currency'] ?? 'ج.م',
        ];
    }

    /**
     * إصدار عرض السعر: يُحفظ في الطلب، تتغيّر حالته، ويُرسل للعميل بالبريد فوراً.
     * $send = false يحفظ العرض دون إرسال بريد (للمراجعة قبل الإرسال).
     */
    public function issueQuote(bool $send = true): void
    {
        $sr = static::baseQuery()->whereKey($this->editingId)->firstOrFail();

        $items = array_values(array_filter(
            $this->draft['items'] ?? [],
            fn ($i) => trim((string) ($i['name'] ?? '')) !== ''
        ));

        if (! $items) {
            Notification::make()->title('أضف بنداً واحداً على الأقل باسم واضح.')->danger()->send();

            return;
        }

        $payload = (array) $sr->payload;
        $payload['_quote'] = [
            'items' => array_map(fn ($i) => [
                'name' => trim((string) $i['name']),
                'desc' => trim((string) ($i['desc'] ?? '')),
                'qty' => max(1, (int) ($i['qty'] ?? 1)),
                'price' => max(0, (float) ($i['price'] ?? 0)),
            ], $items),
            'discount' => max(0, (float) ($this->draft['discount'] ?? 0)),
            'vat_percent' => max(0, (float) ($this->draft['vat_percent'] ?? 0)),
            'currency' => trim((string) ($this->draft['currency'] ?? 'ج.م')) ?: 'ج.م',
            'valid_days' => max(1, (int) ($this->draft['valid_days'] ?? 30)),
            'timeline' => trim((string) ($this->draft['timeline'] ?? '')),
            'notes' => trim((string) ($this->draft['notes'] ?? '')),
            'issued_at' => ($payload['_quote']['issued_at'] ?? null) && ! $send
                ? $payload['_quote']['issued_at']
                : now()->toIso8601String(),
        ];

        // إصدار العرض ينقل مسار الطلب تلقائياً إلى مرحلة اعتماد العميل
        $flow = $payload['_flow'] ?? [];
        if (($flow['stage'] ?? null) !== 'in_progress' && ($flow['stage'] ?? null) !== 'delivered') {
            $flow['stage'] = 'awaiting_approval';
            $payload['_flow'] = $flow;
        }

        $sr->update(['payload' => $payload, 'status' => 'proposal']);

        $mailed = false;

        if ($send && filter_var($sr->email, FILTER_VALIDATE_EMAIL)) {
            // الطلب محفوظ بالفعل؛ فشل البريد يُبلَّغ ولا يُسقط العملية (دستور §3)
            try {
                Mail::to($sr->email)->send(new QuoteProposalIssued($sr->fresh()));
                $mailed = true;
            } catch (\Throwable $e) {
                report($e);
                Notification::make()
                    ->title('صدر العرض، لكن تعذّر إرسال البريد')
                    ->body('راجع إعدادات البريد ثم أعد الإرسال.')
                    ->warning()
                    ->send();
            }
        }

        if ($mailed) {
            Notification::make()
                ->title('صدر عرض السعر '.$sr->reference)
                ->body('أُرسل إلى '.$sr->email.' وظهر للعميل في صفحة الطلب.')
                ->success()
                ->send();
        } elseif (! $send) {
            Notification::make()->title('حُفظ عرض السعر دون إرسال بريد.')->success()->send();
        } elseif (! filter_var($sr->email, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('صدر العرض وظهر للعميل')
                ->body('لا يوجد بريد إلكتروني مسجّل لهذا الطلب فلم يُرسل بريد.')
                ->warning()
                ->send();
        }

        $this->closeQuote();
    }

    /** تحديث مسار الطلب: المرحلة وتواريخها. */
    private function saveFlow(int $id, array $changes): ServiceRequest
    {
        $sr = static::baseQuery()->whereKey($id)->firstOrFail();
        $payload = (array) $sr->payload;
        $payload['_flow'] = array_merge($payload['_flow'] ?? [], $changes);
        $sr->update(['payload' => $payload]);

        return $sr->fresh();
    }

    /** تثبيت موعد الاجتماع التعريفي. */
    public function setMeeting(int $id): void
    {
        $when = trim((string) ($this->flowInput[$id]['meeting_at'] ?? ''));

        if ($when === '') {
            Notification::make()->title('حدّد تاريخ ووقت الاجتماع أولاً.')->danger()->send();

            return;
        }

        $sr = $this->saveFlow($id, [
            'stage' => 'meeting_scheduled',
            'meeting_at' => Carbon::parse($when)->toIso8601String(),
        ]);

        Notification::make()->title('ثُبّت موعد الاجتماع للطلب '.$sr->reference)->success()->send();
    }

    /** انتهاء الاجتماع: تبدأ مهلة الـ3 أيام لتسليم عرض السعر. */
    public function meetingDone(int $id): void
    {
        $sr = $this->saveFlow($id, [
            'stage' => 'quote_due',
            'meeting_done_at' => now()->toIso8601String(),
        ]);

        $due = QuoteController::deadlineFor(now());

        Notification::make()
            ->title('بدأت مهلة تجهيز عرض السعر')
            ->body('موعد التسليم: '.$due->format('Y/m/d — H:i'))
            ->success()
            ->send();
    }

    /** اعتماد العميل للعرض وبدء التنفيذ حتى موعد التسليم. */
    public function startExecution(int $id): void
    {
        $due = trim((string) ($this->flowInput[$id]['due_at'] ?? ''));

        if ($due === '') {
            Notification::make()->title('حدّد موعد التسليم المتوقع أولاً.')->danger()->send();

            return;
        }

        $sr = $this->saveFlow($id, [
            'stage' => 'in_progress',
            'approved_at' => now()->toIso8601String(),
            'started_at' => now()->toIso8601String(),
            'due_at' => Carbon::parse($due)->toIso8601String(),
        ]);

        $sr->update(['status' => 'won']);

        Notification::make()->title('بدأ تنفيذ المتجر للطلب '.$sr->reference)->success()->send();
    }

    /** تسليم المتجر — نهاية المسار. */
    public function markDelivered(int $id): void
    {
        $sr = $this->saveFlow($id, [
            'stage' => 'delivered',
            'delivered_at' => now()->toIso8601String(),
        ]);

        Notification::make()->title('تم تسليم المتجر — الطلب '.$sr->reference)->success()->send();
    }

    /** إرجاع الطلب لمرحلة سابقة عند الحاجة. */
    public function resetStage(int $id, string $stage): void
    {
        abort_unless(array_key_exists($stage, QuoteController::STAGES), 422);

        $sr = $this->saveFlow($id, ['stage' => $stage]);

        Notification::make()->title('أُعيد الطلب '.$sr->reference.' إلى مرحلة: '.QuoteController::STAGES[$stage]['label'])->success()->send();
    }

    public function deleteQuote(int $id): void
    {
        $sr = static::baseQuery()->whereKey($id)->firstOrFail();
        $payload = (array) $sr->payload;
        unset($payload['_quote']);
        $sr->update(['payload' => $payload, 'status' => 'contacted']);

        Notification::make()->title('حُذف عرض السعر من الطلب '.$sr->reference)->success()->send();
    }

    public function markStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, ServiceRequest::STATUSES, true), 422);

        $sr = static::baseQuery()->whereKey($id)->firstOrFail();
        $sr->update([
            'status' => $status,
            'contacted_at' => $status === 'contacted' ? now() : $sr->contacted_at,
        ]);

        Notification::make()->title('تم تحديث حالة الطلب '.$sr->reference)->success()->send();
    }

    public function deleteRequest(int $id): void
    {
        $sr = static::baseQuery()->whereKey($id)->firstOrFail();
        $reference = $sr->reference;
        $invite = str_starts_with((string) $sr->source, 'quote_link:');
        $sr->delete();

        Notification::make()
            ->title('تم حذف الطلب '.$reference)
            ->body($invite ? 'الرابط المخصّص أصبح متاحاً لاستقبال طلب جديد.' : null)
            ->success()
            ->send();
    }
}
