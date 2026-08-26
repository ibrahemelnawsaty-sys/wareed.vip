<?php

namespace App\Console\Commands;

use App\Http\Controllers\QuoteController;
use App\Models\ServiceRequest;
use Illuminate\Console\Command;

/**
 * حذف طلبات نموذج المتاجر — لتنظيف الطلبات التجريبية وإعادة فتح الروابط المخصّصة.
 *
 *   php artisan quote:reset hajar-salama   حذف طلبات رابط مخصّص
 *   php artisan quote:reset --all          حذف كل طلبات النموذج
 *   php artisan quote:reset --id=3         حذف طلب بعينه
 */
class QuoteReset extends Command
{
    protected $signature = 'quote:reset
                            {invite? : اسم الرابط المخصّص، مثل hajar-salama}
                            {--id=* : أرقام طلبات محدّدة للحذف}
                            {--all : حذف كل طلبات نموذج المتاجر}
                            {--force : تنفيذ الحذف دون سؤال التأكيد}';

    protected $description = 'حذف طلبات نموذج المتاجر وإعادة فتح الروابط المخصّصة';

    public function handle(): int
    {
        $invite = $this->argument('invite');
        $ids = $this->option('id');

        $query = ServiceRequest::query()
            ->where(fn ($q) => $q->where('source', 'quote_form')->orWhere('source', 'like', 'quote_link:%'));

        if ($ids) {
            $query->whereIn('id', $ids);
        } elseif ($invite) {
            if (! array_key_exists($invite, QuoteController::INVITES)) {
                $this->error("الرابط المخصّص «{$invite}» غير معرّف في QuoteController::INVITES.");

                return self::FAILURE;
            }
            $query->where('source', 'quote_link:'.$invite);
        } elseif (! $this->option('all')) {
            $this->error('حدّد رابطاً مخصّصاً، أو --id=، أو --all.');

            return self::FAILURE;
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('لا توجد طلبات مطابقة — لا شيء للحذف.');

            return self::SUCCESS;
        }

        $this->table(
            ['#', 'الرقم المرجعي', 'العميل', 'المصدر', 'التاريخ'],
            $rows->map(fn ($r) => [
                $r->id, $r->reference, $r->name, $r->source, $r->created_at?->format('Y-m-d H:i'),
            ])->all()
        );

        if (! $this->option('force') && ! $this->confirm('حذف هذه الطلبات نهائياً؟')) {
            $this->info('أُلغي الحذف.');

            return self::SUCCESS;
        }

        $count = $rows->count();
        ServiceRequest::whereIn('id', $rows->pluck('id'))->delete();

        $this->info("تم حذف {$count} طلب/طلبات. الروابط المخصّصة المرتبطة بها صارت متاحة من جديد.");

        return self::SUCCESS;
    }
}
