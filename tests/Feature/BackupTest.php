<?php

use App\Filament\Pages\Backups;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\User;
use App\Support\Backup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function backupAdmin(string $role = 'super_admin'): User
{
    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

/** قرصان مزيّفان وملفّا عميل عليهما، كما هي حال الخادم. */
function backupFakeDisks(): void
{
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('local')->put('contracts/WRD-CTR-2026-00001/company.pdf', 'عقد موقّع من الشركة');
    Storage::disk('local')->put('branding/stamp.png', 'ختم');
    Storage::disk('public')->put('stores/logo.png', 'شعار متجر');
}

it('ينشئ نسخة تضمّ كل الجداول وملفات القرصين ولا تضمّ النسخ نفسها', function () {
    backupFakeDisks();
    ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'company' => 'متجر حواديت',
        'phone' => '—', 'email' => 'h@example.test', 'status' => 'new', 'source' => 'quote_form',
    ]);

    $row = Backup::create('قبل تعديل سجلّ الإصدارات');

    expect($row['size'])->toBeGreaterThan(0)
        ->and($row['manifest']['note'])->toBe('قبل تعديل سجلّ الإصدارات')
        ->and($row['manifest']['files'])->toBe(3)
        ->and($row['manifest']['tables'])->toHaveKey('service_requests')
        ->and($row['manifest']['tables']['service_requests'])->toBe(1)
        // الجداول المتطايرة (كاش وجلسات وطوابير) خارج النسخة
        ->and($row['manifest']['tables'])->not->toHaveKey('cache')
        ->and($row['manifest']['tables'])->not->toHaveKey('sessions');

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path(Backup::DIR.'/'.$row['name']));

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entries[] = $zip->getNameIndex($i);
    }
    $zip->close();

    expect($entries)->toContain('manifest.json')
        ->toContain('database/tables/service_requests.json')
        ->toContain('database/tables/settings.json')
        ->toContain('storage/local/contracts/WRD-CTR-2026-00001/company.pdf')
        ->toContain('storage/public/stores/logo.png');

    // نسخة ثانية لا تبتلع الأولى: مجلّد النسخ مستثنى من النسخ
    $second = Backup::create();
    expect($second['manifest']['files'])->toBe(3)
        ->and($second['name'])->not->toBe($row['name']);
});

it('يرجع بالبيانات والملفات إلى لحظة النسخة، ويترك نسخة أمان قبل الاسترجاع', function () {
    backupFakeDisks();
    Setting::create(['key' => 'contact_email', 'value' => ['ar' => 'info@wareed.vip']]);
    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'company' => 'متجر حواديت',
        'phone' => '—', 'email' => 'h@example.test', 'status' => 'new', 'source' => 'quote_form',
        'payload' => ['_quote' => ['total' => 15000]],
    ]);

    $backup = Backup::create()['name'];

    // كارثة بعد النسخة: طلب محذوف، إعداد مُتلَف، ملف ضائع، وطلب جديد
    $sr->delete();
    Setting::where('key', 'contact_email')->update(['value' => json_encode(['ar' => 'خطأ'])]);
    Storage::disk('local')->delete('branding/stamp.png');
    ServiceRequest::create([
        'service_type' => 'training', 'name' => 'طلب بعد النسخة', 'company' => '—',
        'phone' => '—', 'email' => 'after@example.test', 'status' => 'new', 'source' => 'service_training',
    ]);

    $result = Backup::restore($backup);

    expect($result['source'])->toBe('json')   // قاعدة الاختبارات في الذاكرة فلا ملف SQLite يُنسخ
        ->and($result['files'])->toBe(3)
        ->and(ServiceRequest::query()->count())->toBe(1)
        ->and(ServiceRequest::query()->first()->name)->toBe('أ. هاجر سلامة')
        ->and(ServiceRequest::query()->first()->payload['_quote']['total'])->toBe(15000)
        ->and(Setting::where('key', 'contact_email')->first()->getTranslation('value', 'ar'))->toBe('info@wareed.vip')
        ->and(Storage::disk('local')->get('branding/stamp.png'))->toBe('ختم')
        ->and(Storage::disk('public')->get('stores/logo.png'))->toBe('شعار متجر');

    // نسخة الأمان موجودة وتحمل حال ما قبل الاسترجاع (الطلب الجديد موجود فيها، والقديم لا)
    expect(Backup::find($result['safety']))->not->toBeNull();

    Backup::restore($result['safety']);

    expect(ServiceRequest::query()->count())->toBe(1)
        ->and(ServiceRequest::query()->first()->name)->toBe('طلب بعد النسخة')
        ->and(Storage::disk('local')->exists('branding/stamp.png'))->toBeFalse();
});

it('يحفظ ملف SQLite نفسه ويستعيده بأمانة تامة', function () {
    backupFakeDisks();

    // قاعدة على ملف حقيقي كحال الخادم — لا في الذاكرة كحال الاختبارات.
    // معاملة RefreshDatabase تُنهى أولاً وإلا بقيت مفتوحة على قاعدة الذاكرة فأفسدت ما بعدها.
    DB::rollBack();
    $file = tempnam(sys_get_temp_dir(), 'wrdsqlite').'.sqlite';
    file_put_contents($file, '');
    config(['database.connections.sqlite.database' => $file]);
    DB::purge('sqlite');
    Artisan::call('migrate', ['--force' => true]);

    Setting::create(['key' => 'contact_email', 'value' => ['ar' => 'info@wareed.vip']]);

    $row = Backup::create();
    expect($row['manifest']['has_sqlite_file'])->toBeTrue();

    Setting::where('key', 'contact_email')->delete();
    expect(Setting::query()->count())->toBe(0);

    $result = Backup::restore($row['name']);

    expect($result['source'])->toBe('sqlite')
        ->and(Setting::where('key', 'contact_email')->first()->getTranslation('value', 'ar'))->toBe('info@wareed.vip');

    DB::disconnect();
    config(['database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    @unlink($file);
});

it('يرفض ملفاً ليس نسخة احتياطية صالحة', function () {
    backupFakeDisks();

    expect(fn () => Backup::store(UploadedFile::fake()->createWithContent('نسخة.zip', 'ليس ملفاً مضغوطاً')))
        ->toThrow(RuntimeException::class);

    expect(Backup::all())->toBeEmpty();
});

it('يبقي أحدث النسخ ويحذف ما قبلها، ولا يحذف النسخة قيد الاسترجاع', function () {
    backupFakeDisks();

    $first = Backup::create('الأقدم')['name'];

    for ($i = 0; $i < Backup::KEEP; $i++) {
        Backup::create();
    }

    // النسخة الأقدم خرجت بالتقليم بعد تجاوز الحدّ
    expect(Backup::all())->toHaveCount(Backup::KEEP)
        ->and(Backup::find($first))->toBeNull();

    // ونسخة أقدم يجري استرجاعها لا تحذفها نسخة الأمان المأخوذة قبله
    $oldest = Backup::all()[Backup::KEEP - 1]['name'];
    Backup::restore($oldest);

    // النسخة المسترجَعة بقيت فوق الحدّ مؤقّتاً — يزيحها التقليم مع النسخة التالية
    expect(Backup::find($oldest))->not->toBeNull()
        ->and(Backup::all())->toHaveCount(Backup::KEEP + 1);

    Backup::create();

    expect(Backup::all())->toHaveCount(Backup::KEEP)
        ->and(Backup::find($oldest))->toBeNull();
});

it('صفحة النسخ الاحتياطية لمدير النظام وحده', function () {
    backupFakeDisks();

    $this->actingAs(backupAdmin('staff'));
    expect(Backups::canAccess())->toBeFalse();

    $this->actingAs(backupAdmin());
    expect(Backups::canAccess())->toBeTrue();

    Livewire::test(Backups::class)
        ->set('note', 'نسخة من اللوحة')
        ->call('create')
        ->assertHasNoErrors();

    $rows = Backup::all();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['manifest']['note'])->toBe('نسخة من اللوحة')
        ->and(Backups::summary($rows[0]))->toContain('ملفاً')
        ->and(Backups::size(2_097_152))->toBe('2.0 ميغابايت');

    // الحذف من اللوحة يمسح الملف فعلاً
    Livewire::test(Backups::class)->call('remove', $rows[0]['name']);
    expect(Backup::all())->toBeEmpty();
});

it('يقبل نسخة يرفعها الفريق من جهازه فتصير قابلة للاسترجاع', function () {
    backupFakeDisks();
    $this->actingAs(backupAdmin());
    Setting::create(['key' => 'contact_email', 'value' => ['ar' => 'info@wareed.vip']]);

    // نسخة أُخذت ثم نُزّلت إلى جهاز الفريق، ولا أثر لها على الخادم
    $taken = Backup::create('نسخة نُزّلت')['name'];
    $bytes = Storage::disk('local')->get(Backup::DIR.'/'.$taken);
    Backup::delete($taken);
    Setting::where('key', 'contact_email')->delete();

    expect(Backup::all())->toBeEmpty();

    Livewire::test(Backups::class)
        ->set('upload', UploadedFile::fake()->createWithContent('نسخة-وريد.zip', $bytes))
        ->call('saveUpload')
        ->assertHasNoErrors();

    $rows = Backup::all();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['manifest']['note'])->toBe('نسخة نُزّلت');

    Backup::restore($rows[0]['name']);

    expect(Setting::where('key', 'contact_email')->first()->getTranslation('value', 'ar'))->toBe('info@wareed.vip');
});

it('يأخذ نسخة من سطر الأوامر ويعرض المحفوظ', function () {
    backupFakeDisks();

    $this->artisan('backup:run', ['--list' => true])->expectsOutputToContain('لا نسخ محفوظة بعد.')->assertSuccessful();

    $this->artisan('backup:run', ['--note' => 'نسخة يومية'])->assertSuccessful();

    $rows = Backup::all();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['manifest']['note'])->toBe('نسخة يومية');

    $this->artisan('backup:run', ['--list' => true])->expectsOutputToContain('نسخة يومية')->assertSuccessful();
});
