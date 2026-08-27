<?php

namespace App\Filament\Pages;

use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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
                    'payload' => (array) $sr->payload,
                    'status' => $sr->status,
                    'invite' => $invite,
                    'created' => $sr->created_at,
                    'deadline' => $sr->created_at?->copy()->addHours(QuoteController::SLA_HOURS),
                    'overdue' => $sr->status === 'new'
                        && $sr->created_at?->copy()->addHours(QuoteController::SLA_HOURS)->isPast(),
                    'document' => $invite
                        ? route('quote.document', $invite)
                        : URL::signedRoute('quote.document.signed', ['serviceRequest' => $sr->id]),
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
                    && $r->created_at?->copy()->addHours(QuoteController::SLA_HOURS)->isPast()
            )->count(),
        ];
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
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
