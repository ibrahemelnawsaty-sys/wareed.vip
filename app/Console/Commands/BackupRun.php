<?php

namespace App\Console\Commands;

use App\Filament\Pages\Backups;
use App\Support\Backup;
use Illuminate\Console\Command;
use Throwable;

/**
 * نسخة احتياطية من سطر الأوامر — لمن أراد جدولتها على الخادم بدل الضغط على زرّ اللوحة.
 *
 *   php artisan backup:run                          نسخة الآن
 *   php artisan backup:run --note="قبل النشر"        نسخة بملاحظة
 *   php artisan backup:run --list                   عرض النسخ المحفوظة
 *
 * للجدولة اليومية من لوحة الاستضافة (Cron Jobs) الساعة الثالثة فجراً:
 *   cd ~/public_html && php artisan backup:run --note="نسخة يومية"
 */
class BackupRun extends Command
{
    protected $signature = 'backup:run
                            {--note= : ملاحظة تُحفظ داخل النسخة لتمييز سببها}
                            {--list : عرض النسخ المحفوظة بدل أخذ نسخة جديدة}';

    protected $description = 'أخذ نسخة احتياطية كاملة من قاعدة البيانات وملفات العملاء';

    public function handle(): int
    {
        if ($this->option('list')) {
            $rows = Backup::all();

            if (empty($rows)) {
                $this->warn('لا نسخ محفوظة بعد.');

                return self::SUCCESS;
            }

            $this->table(
                ['النسخة', 'التاريخ', 'الحجم', 'المحتوى', 'الملاحظة'],
                array_map(fn ($row) => [
                    $row['name'],
                    $row['created_at']->format('Y/m/d — H:i'),
                    Backups::size($row['size']),
                    Backups::summary($row),
                    $row['manifest']['note'] ?? '',
                ], $rows)
            );

            return self::SUCCESS;
        }

        try {
            $row = Backup::create((string) $this->option('note'));
        } catch (Throwable $e) {
            report($e);
            $this->error('تعذّر أخذ النسخة: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('أُخذت نسخة احتياطية: '.$row['name']);
        $this->line('  '.Backups::summary($row).' — '.Backups::size($row['size']));
        $this->line('  المسار: storage/app/private/'.Backup::DIR.'/'.$row['name']);

        return self::SUCCESS;
    }
}
