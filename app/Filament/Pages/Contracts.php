<?php

namespace App\Filament\Pages;

use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;
use App\Support\Contracts as ContractFlow;
use App\Support\ServiceFlow;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * صفحة العقود: مسوّدات العقود المقترحة تلقائياً بعد اعتماد العملاء لعروض الأسعار،
 * تحرير بنودها (إضافة/تعديل/حذف/ترتيب)، إرسالها للمراجعة، متابعة ملاحظات العميل
 * وإعادة الإرسال بعد التعديل حتى الاعتماد النهائي.
 */
class Contracts extends Page
{
    protected string $view = 'filament.pages.contracts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الموقع';

    protected static ?string $navigationLabel = 'العقود';

    protected static ?int $navigationSort = 2;

    public ?string $filter = 'all';

    /** الطلب المفتوح حالياً في محرّر بنود العقد (null = المحرّر مغلق). */
    public ?int $open = null;

    /** بنود العقد الجاري تحريرها. */
    public array $clauses = [];

    public function getTitle(): string
    {
        return 'العقود';
    }

    public static function getNavigationBadge(): ?string
    {
        $waiting = static::baseQuery()->get()
            ->filter(fn (ServiceRequest $sr) => (ContractFlow::of($sr)['status'] ?? null) === 'feedback')
            ->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    /** طلبات الخدمات الثلاث التي لها عقد، أو اعتمد أصحابها عرض السعر فصار العقد خطوتها التالية. */
    protected static function baseQuery()
    {
        return ServiceRequest::query()
            ->whereIn('service_type', ServiceFlow::TYPES)
            ->where(fn ($q) => $q->where('payload', 'like', '%"_contract"%')->orWhere('payload', 'like', '%"choice":"approved"%'));
    }

    public function mount(): void
    {
        $open = request()->integer('open');

        if ($open > 0) {
            $this->openContract($open);
        }
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /** @return array<int, array<string, mixed>> */
    public function getRowsProperty(): array
    {
        return static::baseQuery()
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(function (ServiceRequest $sr) {
                $contract = ContractFlow::of($sr);

                return [
                    'id' => $sr->id,
                    'reference' => $sr->reference,
                    'name' => $sr->name,
                    'company' => $sr->company,
                    'email' => $sr->email,
                    'phone' => $sr->phone && $sr->phone !== '—' ? $sr->phone : null,
                    'quote' => QuoteController::quoteOf($sr),
                    'decision' => QuoteController::decisionOf($sr),
                    'flow' => QuoteController::flowOf($sr),
                    'contract' => $contract,
                    'review_url' => $contract ? ContractFlow::reviewUrl($sr) : null,
                    'service_label' => ServiceFlow::label($sr),
                    'company_label' => ServiceFlow::profile($sr)['company_label'],
                ];
            })
            ->filter(fn (array $row) => match ($this->filter) {
                'draft' => ($row['contract']['status'] ?? 'none') === 'draft' || $row['contract'] === null,
                'sent' => ($row['contract']['status'] ?? null) === 'sent',
                'feedback' => ($row['contract']['status'] ?? null) === 'feedback',
                'approved' => ($row['contract']['status'] ?? null) === 'approved',
                default => true,
            })
            ->values()
            ->all();
    }

    /** @return array<string, int> */
    public function getStatsProperty(): array
    {
        $statuses = static::baseQuery()->get()->map(fn (ServiceRequest $sr) => ContractFlow::of($sr)['status'] ?? 'none');

        return [
            'total' => $statuses->count(),
            'draft' => $statuses->filter(fn ($s) => in_array($s, ['draft', 'none'], true))->count(),
            'sent' => $statuses->filter(fn ($s) => $s === 'sent')->count(),
            'feedback' => $statuses->filter(fn ($s) => $s === 'feedback')->count(),
            'approved' => $statuses->filter(fn ($s) => $s === 'approved')->count(),
        ];
    }

    /** بيانات التوقيع والختم الحالية — تُضبط من إعدادات الموقع. */
    public function getSignatureProperty(): array
    {
        return ContractFlow::signature();
    }

    /** فتح محرّر البنود لطلب — وإنشاء مسوّدة العقد المقترحة إن لم تكن قد أُنشئت بعد. */
    public function openContract(int $id): void
    {
        $sr = static::baseQuery()->whereKey($id)->first()
            ?? ServiceRequest::query()->whereKey($id)->firstOrFail();

        if (! ContractFlow::of($sr) && ! QuoteController::quoteOf($sr)) {
            Notification::make()
                ->title('لا يمكن إنشاء عقد قبل إصدار عرض سعر لهذا الطلب')
                ->body('أصدر عرض السعر من صفحة متابعة الطلبات أولاً؛ تُبنى بنود العقد من بنوده ودفعاته.')
                ->danger()
                ->send();

            return;
        }

        $created = ! ContractFlow::of($sr);
        $contract = ContractFlow::createDraft($sr);

        $this->open = $sr->id;
        $this->clauses = array_map(fn (array $c) => [
            'id' => $c['id'],
            'section' => $c['section'],
            'title' => $c['title'],
            'body' => $c['body'],
            'decision' => $c['decision'],
            'decision_label' => $c['decision_label'],
            'note' => $c['note'],
            'locked' => $c['locked'],
        ], $contract['clauses']);

        if ($created) {
            Notification::make()
                ->title('أُنشئت مسوّدة العقد '.$contract['number'])
                ->body('راجع البنود المقترحة وعدّلها بما يناسب المشروع ثم أرسلها للعميل لمراجعتها.')
                ->success()
                ->send();
        }
    }

    public function closeContract(): void
    {
        $this->open = null;
        $this->clauses = [];
    }

    public function addClause(): void
    {
        $last = end($this->clauses) ?: [];

        $this->clauses[] = [
            'id' => '',
            'section' => (string) ($last['section'] ?? ''),
            'title' => '',
            'body' => '',
            'decision' => null,
            'decision_label' => null,
            'note' => '',
            'locked' => false,
        ];
    }

    public function removeClause(int $index): void
    {
        unset($this->clauses[$index]);
        $this->clauses = array_values($this->clauses);
    }

    /** نقل بند خطوة لأعلى أو لأسفل. */
    public function moveClause(int $index, int $direction): void
    {
        $to = $index + ($direction < 0 ? -1 : 1);

        if (! isset($this->clauses[$index], $this->clauses[$to])) {
            return;
        }

        [$this->clauses[$index], $this->clauses[$to]] = [$this->clauses[$to], $this->clauses[$index]];
        $this->clauses = array_values($this->clauses);
    }

    /**
     * حفظ البنود المحرَّرة. أي بند تغيّر نصّه يفقد قرار العميل السابق عليه — ولو كان
     * معتمداً — فيُطلب قراره فيه من جديد عند إعادة الإرسال.
     */
    public function saveClauses(bool $andSend = false): void
    {
        $sr = ServiceRequest::query()->whereKey($this->open)->firstOrFail();

        $clauses = array_values(array_filter(
            $this->clauses,
            fn ($c) => trim((string) ($c['title'] ?? '')) !== '' || trim((string) ($c['body'] ?? '')) !== ''
        ));

        if (! $clauses) {
            Notification::make()->title('أضف بنداً واحداً على الأقل بعنوان ونص.')->danger()->send();

            return;
        }

        foreach ($clauses as $c) {
            if (trim((string) ($c['title'] ?? '')) === '' || trim((string) ($c['body'] ?? '')) === '') {
                Notification::make()->title('لكل بند عنوان ونص — أكمل البنود الناقصة أو احذفها.')->danger()->send();

                return;
            }
        }

        $contract = ContractFlow::saveClauses($sr, $clauses);

        if ($andSend) {
            $this->sendContract($sr->id);

            return;
        }

        $unlocked = $contract['count'] - $contract['approved_count'];

        Notification::make()
            ->title('حُفظت بنود العقد '.$contract['number'])
            ->body($contract['is_sent']
                ? $unlocked.' بنداً ستُعرض على العميل للقرار عند إعادة الإرسال، و'.$contract['approved_count'].' معتمدة تبقى كما هي.'
                : $contract['count'].' بنداً جاهزة للإرسال إلى العميل.')
            ->success()
            ->send();

        $this->openContract($sr->id);
    }

    /** إرسال المسوّدة للمراجعة (أول مرة) أو إعادة إرسالها بعد التعديل. */
    public function sendContract(int $id): void
    {
        $sr = ServiceRequest::query()->whereKey($id)->firstOrFail();
        $before = ContractFlow::of($sr);

        if (! $before) {
            Notification::make()->title('لا توجد مسوّدة عقد لهذا الطلب بعد.')->danger()->send();

            return;
        }

        if ($before['is_approved']) {
            Notification::make()->title('اعتمد العميل هذا العقد بالفعل ولا يُعاد إرساله للمراجعة.')->warning()->send();

            return;
        }

        $mailed = ContractFlow::send($sr);
        $contract = ContractFlow::of($sr->fresh());
        $resend = $contract['round'] > 1;

        if ($mailed) {
            Notification::make()
                ->title(($resend ? 'أُعيد إرسال العقد بعد التعديل — الجولة ' : 'أُرسلت مسوّدة العقد للمراجعة — الجولة ').$contract['round'])
                ->body('وصل إلى '.$sr->email.' برابط مراجعة البنود واعتمادها.')
                ->success()
                ->send();
        } elseif (! filter_var((string) $sr->email, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('فُتحت جولة المراجعة، لكن لا بريد إلكتروني للعميل')
                ->body('شارك معه رابط العقد يدوياً من زر «رابط العميل».')
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('فُتحت جولة المراجعة، لكن تعذّر إرسال البريد الإلكتروني')
                ->body('راجع إعدادات MAIL ثم أعد الإرسال.')
                ->warning()
                ->send();
        }

        if ($this->open === $id) {
            $this->closeContract();
        }
    }

    /** حذف مسوّدة عقد لم يعتمدها العميل — لإعادة توليدها من عرض سعر مُحدَّث مثلاً. */
    public function deleteContract(int $id): void
    {
        $sr = ServiceRequest::query()->whereKey($id)->firstOrFail();
        $contract = ContractFlow::of($sr);

        if ($contract && $contract['is_approved']) {
            Notification::make()->title('لا يُحذف عقد اعتمده العميل.')->danger()->send();

            return;
        }

        $payload = (array) $sr->payload;
        unset($payload['_contract']);
        $sr->update(['payload' => $payload]);

        if ($this->open === $id) {
            $this->closeContract();
        }

        Notification::make()->title('حُذفت مسوّدة العقد للطلب '.$sr->reference)->success()->send();
    }
}
