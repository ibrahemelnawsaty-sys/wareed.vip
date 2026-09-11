<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * النسخ الاحتياطية: لقطة كاملة من بيانات الموقع (قاعدة البيانات وملفات العملاء)
 * في ملف مضغوط واحد يمكن تنزيله والرجوع إليه في أي وقت.
 *
 * النسخة تحمل صورتين من قاعدة البيانات: ملف SQLite نفسه بأمانة تامة إن كانت هي
 * محرّك القاعدة، وتفريغاً بصيغة JSON لكل جدول يعمل مع أي محرّك. الاسترجاع يفضّل
 * الملف الخام ويقع على التفريغ عند غيابه، فالنسخة صالحة للرجوع حتى لو تغيّر
 * المحرّك بين وقت أخذها ووقت استرجاعها.
 *
 * ما لا يدخل النسخة عمداً: ملف `.env` (فيه كلمات المرور، ومكانه الخادم وحده)،
 * ومجلّد `vendor/` وشيفرة المشروع — تلك في Git لا في نسخة البيانات.
 */
class Backup
{
    /** مجلّد النسخ داخل القرص الخاص `local`. */
    public const DIR = 'backups';

    /** أقصى عدد نسخ محفوظة على الخادم؛ الأقدم يُحذف تلقائياً بعد كل نسخة جديدة. */
    public const KEEP = 12;

    /** أقصى حجم لملف نسخة تُرفع من جهاز الفريق (كيلوبايت). */
    public const UPLOAD_MAX_KB = 51200;

    /** مجلّدات لا تدخل النسخة: النسخ نفسها (تكرار لا ينتهي)، وملفات الرفع المؤقّتة. */
    protected const SKIP = [self::DIR, 'livewire-tmp'];

    /** جداول لا معنى لحفظها ولا لاسترجاعها: كاش وطوابير وجلسات. */
    protected const VOLATILE = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    /** الأقراص التي تُحفظ ملفاتها داخل النسخة. */
    protected const DISKS = ['local', 'public'];

    /**
     * ينشئ نسخة جديدة ويعيد سطرها كما تعرضه اللوحة.
     *
     * @param  string|null  $protect  نسخة لا يحذفها التقليم مهما بلغ عمرها (النسخة قيد الاسترجاع).
     */
    public static function create(string $note = '', ?string $protect = null): array
    {
        $name = static::name();
        $tmp = tempnam(sys_get_temp_dir(), 'wrd').'.zip';

        $zip = new ZipArchive;

        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('تعذّر إنشاء ملف النسخة الاحتياطية.');
        }

        $tables = [];

        foreach (static::tables() as $table) {
            $rows = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
            $tables[$table] = count($rows);
            $zip->addFromString("database/tables/{$table}.json", (string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $sqlite = static::sqlitePath();

        if ($sqlite !== null) {
            rescue(fn () => DB::statement('PRAGMA wal_checkpoint(TRUNCATE)'), null, false);
            $zip->addFile($sqlite, 'database/database.sqlite');
        }

        $files = 0;

        foreach (static::DISKS as $disk) {
            foreach (static::filesOf($disk) as $path) {
                $absolute = rescue(fn () => Storage::disk($disk)->path($path), null, false);

                if ($absolute !== null && is_file($absolute)) {
                    $zip->addFile($absolute, "storage/{$disk}/{$path}");
                } else {
                    $zip->addFromString("storage/{$disk}/{$path}", (string) Storage::disk($disk)->get($path));
                }

                $files++;
            }
        }

        $zip->addFromString('manifest.json', (string) json_encode([
            'created_at' => now()->toIso8601String(),
            'note' => $note,
            'app_url' => config('app.url'),
            'app_env' => config('app.env'),
            'php' => PHP_VERSION,
            'driver' => DB::connection()->getDriverName(),
            'has_sqlite_file' => $sqlite !== null,
            'tables' => $tables,
            'files' => $files,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $zip->close();

        $stream = fopen($tmp, 'r');
        Storage::disk('local')->writeStream(static::DIR.'/'.$name, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        @unlink($tmp);

        static::prune($protect);

        return static::find($name) ?? ['name' => $name, 'size' => 0, 'created_at' => now(), 'manifest' => []];
    }

    /** كل النسخ المحفوظة، الأحدث أولاً. */
    public static function all(): array
    {
        $rows = [];

        foreach (Storage::disk('local')->files(static::DIR) as $path) {
            if (! str_ends_with($path, '.zip')) {
                continue;
            }

            $name = basename($path);

            $rows[] = [
                'name' => $name,
                'size' => (int) Storage::disk('local')->size($path),
                'created_at' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($path))->timezone(config('app.timezone')),
                'manifest' => static::manifest($name),
            ];
        }

        // الأحدث أولاً؛ والاسم يفصل عند تساوي الثانية لأنه يحمل الجزء من الألف منها.
        usort($rows, fn ($a, $b) => [$b['created_at'], $b['name']] <=> [$a['created_at'], $a['name']]);

        return $rows;
    }

    public static function find(string $name): ?array
    {
        $name = basename($name);

        foreach (static::all() as $row) {
            if ($row['name'] === $name) {
                return $row;
            }
        }

        return null;
    }

    /** بطاقة تعريف النسخة كما كُتبت وقت إنشائها. */
    public static function manifest(string $name): array
    {
        $zip = static::open($name);

        if ($zip === null) {
            return [];
        }

        $raw = $zip->getFromName('manifest.json');
        $zip->close();

        return is_string($raw) ? (array) json_decode($raw, true) : [];
    }

    public static function delete(string $name): bool
    {
        return Storage::disk('local')->delete(static::DIR.'/'.basename($name));
    }

    /** رابط تنزيل مؤقّت للنسخة. */
    public static function downloadUrl(string $name): ?string
    {
        return rescue(
            fn () => Storage::disk('local')->temporaryUrl(static::DIR.'/'.basename($name), now()->addMinutes(30)),
            null,
            false
        );
    }

    /** يحفظ ملف نسخة رفعه الفريق من جهازه ليصير قابلاً للاسترجاع. */
    public static function store(UploadedFile $file): string
    {
        $name = static::name('-uploaded');

        $file->storeAs(static::DIR, $name, 'local');

        if (static::open($name) === null) {
            static::delete($name);

            throw new RuntimeException('الملف المرفوع ليس نسخة احتياطية صالحة.');
        }

        return $name;
    }

    /**
     * يرجع بالموقع إلى لحظة النسخة: قاعدة البيانات ثم ملفات العملاء.
     * يأخذ نسخة أمان تلقائية قبل أن يمسّ شيئاً، فالتراجع عن الاسترجاع ممكن بدوره.
     *
     * @return array{safety: string, tables: int, files: int, source: string}
     */
    public static function restore(string $name): array
    {
        $name = basename($name);
        $probe = static::open($name);

        if ($probe === null) {
            throw new RuntimeException('النسخة غير موجودة أو ملفها تالف.');
        }

        $probe->close();

        // النسخة قيد الاسترجاع محميّة من التقليم، وإلا حذفتها نسخة الأمان لو كانت أقدم المحفوظ.
        $safety = static::create('نسخة أمان تلقائية قبل الاسترجاع', $name);

        $zip = static::open($name);

        if ($zip === null) {
            throw new RuntimeException('تعذّر فتح ملف النسخة للاسترجاع.');
        }

        $sqlite = static::sqlitePath();
        $raw = $zip->getFromName('database/database.sqlite');
        $source = 'json';

        if ($sqlite !== null && is_string($raw) && $raw !== '') {
            $zip->close();
            DB::disconnect();
            file_put_contents($sqlite, $raw);
            @unlink($sqlite.'-wal');
            @unlink($sqlite.'-shm');
            $source = 'sqlite';
            $zip = static::open($name);

            if ($zip === null) {
                throw new RuntimeException('تعذّر فتح ملف النسخة لاسترجاع الملفات.');
            }

            $tables = count(static::tables());
        } else {
            $tables = static::restoreTables($zip);
        }

        $files = static::restoreFiles($zip);

        $zip->close();

        return ['safety' => $safety['name'], 'tables' => $tables, 'files' => $files, 'source' => $source];
    }

    /**
     * اسم نسخة جديد: يحمل الجزء من الألف من الثانية فيبقى ترتيبه الأبجدي موافقاً لترتيبه
     * الزمني، ويزيد حتى لا يدهس نسخة أُخذت في اللحظة نفسها.
     */
    protected static function name(string $suffix = ''): string
    {
        $at = now();

        do {
            $name = 'wareed-'.$at->format('Y-m-d-His-v').$suffix.'.zip';
            $at = $at->addMillisecond();
        } while (Storage::disk('local')->exists(static::DIR.'/'.$name));

        return $name;
    }

    /** جداول القاعدة التي تدخل التفريغ، مرتّبة بالاسم. */
    protected static function tables(): array
    {
        $names = array_map(
            fn ($table) => is_array($table) ? ($table['name'] ?? '') : $table->name,
            DB::connection()->getSchemaBuilder()->getTables()
        );

        $names = array_filter($names, fn ($name) => $name !== '' && ! in_array($name, static::VOLATILE, true) && ! str_starts_with($name, 'sqlite_'));

        sort($names);

        return array_values($names);
    }

    /** يعيد تعبئة الجداول من تفريغ JSON — مسار يعمل مع أي محرّك قاعدة. */
    protected static function restoreTables(ZipArchive $zip): int
    {
        $done = 0;

        // بلا قيود مفاتيح أجنبية وداخل معاملة: ترتيب الجداول لا يُسقط الاسترجاع، وفشلُه لا يترك قاعدة نصف ممسوحة.
        Schema::withoutForeignKeyConstraints(function () use ($zip, &$done) {
            DB::transaction(function () use ($zip, &$done) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = (string) $zip->getNameIndex($i);

                    if (! str_starts_with($entry, 'database/tables/') || ! str_ends_with($entry, '.json')) {
                        continue;
                    }

                    $table = basename($entry, '.json');

                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    $rows = (array) json_decode((string) $zip->getFromIndex($i), true);

                    DB::table($table)->delete();

                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }

                    $done++;
                }
            });
        });

        return $done;
    }

    /** يعيد ملفات الأقراص إلى ما كانت عليه: يمسح الموجود (عدا النسخ) ثم يكتب ما في النسخة. */
    protected static function restoreFiles(ZipArchive $zip): int
    {
        foreach (static::DISKS as $disk) {
            foreach (static::filesOf($disk) as $path) {
                Storage::disk($disk)->delete($path);
            }
        }

        $written = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = (string) $zip->getNameIndex($i);

            if (! str_starts_with($entry, 'storage/') || str_ends_with($entry, '/')) {
                continue;
            }

            [, $disk, $path] = array_pad(explode('/', $entry, 3), 3, null);

            if (! in_array($disk, static::DISKS, true) || ! is_string($path) || $path === '') {
                continue;
            }

            if (static::skipped($path)) {
                continue;
            }

            Storage::disk($disk)->put($path, (string) $zip->getFromIndex($i));
            $written++;
        }

        return $written;
    }

    /** ملفات قرصٍ ما عدا المجلّدات المستثناة. */
    protected static function filesOf(string $disk): array
    {
        return array_values(array_filter(
            Storage::disk($disk)->allFiles(),
            fn ($path) => ! static::skipped($path) && basename($path) !== '.gitignore'
        ));
    }

    protected static function skipped(string $path): bool
    {
        foreach (static::SKIP as $dir) {
            if ($path === $dir || str_starts_with($path, $dir.'/')) {
                return true;
            }
        }

        return false;
    }

    /** مسار ملف SQLite إن كان محرّك القاعدة الحالي SQLite وملفه موجوداً. */
    protected static function sqlitePath(): ?string
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return null;
        }

        $path = DB::connection()->getDatabaseName();

        return is_string($path) && is_file($path) ? $path : null;
    }

    protected static function open(string $name): ?ZipArchive
    {
        $path = rescue(fn () => Storage::disk('local')->path(static::DIR.'/'.basename($name)), null, false);

        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        $zip = new ZipArchive;

        return $zip->open($path) === true ? $zip : null;
    }

    /** يبقي أحدث KEEP نسخة ويحذف ما قبلها، عدا نسخة محميّة إن مُرّرت. */
    protected static function prune(?string $protect = null): void
    {
        foreach (array_slice(static::all(), static::KEEP) as $old) {
            if ($protect !== null && $old['name'] === basename($protect)) {
                continue;
            }

            static::delete($old['name']);
        }
    }
}
