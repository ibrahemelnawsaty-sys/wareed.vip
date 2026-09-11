<?php

namespace App\Filament\Pages;

use App\Support\Backup;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Throwable;

/**
 * صفحة النسخ الاحتياطية: لقطة كاملة من بيانات الموقع بضغطة واحدة، وتنزيلها
 * إلى جهاز الفريق، والرجوع إليها وقت الحاجة.
 *
 * الاسترجاع يأخذ نسخة أمان تلقائية قبل أن يمسّ شيئاً، فحتى الاسترجاع الخاطئ
 * له طريق عودة. والصفحة لمدير النظام وحده لأنها تكتب فوق كل بيانات الموقع.
 */
class Backups extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.backups';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'النظام';

    protected static ?string $navigationLabel = 'النسخ الاحتياطية';

    protected static ?int $navigationSort = 1;

    /** ملاحظة اختيارية تُكتب داخل النسخة لتمييز سببها. */
    public string $note = '';

    /** ملف نسخة يرفعه الفريق من جهازه. */
    public $upload = null;

    public function getTitle(): string
    {
        return 'النسخ الاحتياطية';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') || app()->environment('local');
    }

    /** @return array<int, array<string, mixed>> */
    public function getRowsProperty(): array
    {
        return Backup::all();
    }

    public function create(): void
    {
        try {
            $row = Backup::create(trim($this->note));
            $this->note = '';

            Notification::make()
                ->title('أُخذت نسخة احتياطية — '.$row['name'])
                ->body(static::summary($row).' — نزّلها إلى جهازك لتبقى نسخة خارج الخادم.')
                ->success()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()->title('تعذّر أخذ النسخة الاحتياطية.')->body($e->getMessage())->danger()->send();
        }
    }

    public function download(string $name): void
    {
        $url = Backup::downloadUrl($name);

        if ($url === null) {
            Notification::make()->title('تعذّر إنشاء رابط التنزيل.')->danger()->send();

            return;
        }

        $this->js('window.open('.json_encode($url).", '_blank')");
    }

    public function remove(string $name): void
    {
        Backup::delete($name);

        Notification::make()->title('حُذفت النسخة — '.basename($name))->success()->send();
    }

    public function saveUpload(): void
    {
        $this->validate(
            ['upload' => 'required|file|max:'.Backup::UPLOAD_MAX_KB],
            ['upload.required' => 'اختر ملف النسخة أولاً.', 'upload.max' => 'أقصى حجم '.(Backup::UPLOAD_MAX_KB / 1024).' ميغابايت.']
        );

        try {
            $name = Backup::store($this->upload);
            $this->upload = null;
            $this->resetErrorBag();

            Notification::make()->title('رُفعت النسخة — '.$name)->body('صارت جاهزة للاسترجاع من القائمة أدناه.')->success()->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()->title('تعذّر قبول الملف المرفوع.')->body($e->getMessage())->danger()->send();
        }
    }

    public function restore(string $name): void
    {
        try {
            $result = Backup::restore($name);

            Notification::make()
                ->title('رجع الموقع إلى نسخة '.basename($name))
                ->body('استُرجع '.$result['tables'].' جدولاً و'.$result['files'].' ملفاً. نسخة ما قبل الاسترجاع محفوظة باسم '.$result['safety'].'.')
                ->success()
                ->persistent()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()->title('تعذّر الاسترجاع.')->body($e->getMessage())->danger()->persistent()->send();
        }
    }

    /** وصف مختصر لمحتوى نسخة كما يظهر في القائمة. */
    public static function summary(array $row): string
    {
        $manifest = $row['manifest'] ?? [];
        $rows = array_sum($manifest['tables'] ?? []);

        return number_format($rows).' سجلّاً في '.count($manifest['tables'] ?? []).' جدولاً · '
            .number_format($manifest['files'] ?? 0).' ملفاً';
    }

    /** حجم مقروء بالعربية. */
    public static function size(int $bytes): string
    {
        return match (true) {
            $bytes >= 1048576 => number_format($bytes / 1048576, 1).' ميغابايت',
            $bytes >= 1024 => number_format($bytes / 1024, 0).' كيلوبايت',
            default => $bytes.' بايت',
        };
    }
}
