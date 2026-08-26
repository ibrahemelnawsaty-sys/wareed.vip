<?php

use App\Filament\Pages\QuoteRequests;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function adminUser(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

it('يحمّل صفحة دخول الأدمن', function () {
    $this->get('/admin/login')->assertSuccessful();
});

it('يحمّل كل صفحات لوحة الأدمن دون أخطاء', function () {
    $this->actingAs(adminUser());

    $pages = [
        '/admin',
        '/admin/services',
        '/admin/services/create',
        '/admin/pages',
        '/admin/pages/create',
        '/admin/service-requests',
        '/admin/stores',
        '/admin/stores/create',
        '/admin/products',
        '/admin/products/create',
        '/admin/categories',
        '/admin/store-orders',
        '/admin/manage-settings',
        '/admin/quote-requests',
    ];

    foreach ($pages as $url) {
        $this->get($url)->assertSuccessful();
    }
});

it('يحمّل الصفحات العامة', function () {
    $this->get('/')->assertSuccessful();
    $this->get('/sitemap.xml')->assertSuccessful();
    $this->get('/robots.txt')->assertSuccessful();
});

it('يعرض طلبات نموذج المتاجر في لوحة المتابعة ويسمح بحذفها', function () {
    $this->actingAs(adminUser());

    $sr = ServiceRequest::create([
        'service_type' => 'ecommerce', 'name' => 'أ. هاجر سلامة', 'phone' => '—',
        'budget' => 'أكثر من 100 ألف جنيه', 'status' => 'new', 'source' => 'quote_link:hajar-salama',
        'payload' => ['مجال المتجر' => 'عطور ومستحضرات تجميل'],
    ]);

    // طلب من خارج النموذج لا يظهر في اللوحة
    ServiceRequest::create([
        'service_type' => 'training', 'name' => 'طلب تدريب', 'phone' => '0100', 'status' => 'new', 'source' => 'service_training',
    ]);

    Livewire\Livewire::test(QuoteRequests::class)
        ->assertSee($sr->reference)
        ->assertSee('أ. هاجر سلامة')
        ->assertSee('عطور ومستحضرات تجميل')
        ->assertDontSee('طلب تدريب')
        ->call('markStatus', $sr->id, 'proposal')
        ->call('deleteRequest', $sr->id);

    expect(ServiceRequest::find($sr->id))->toBeNull()
        // الطلبات خارج النموذج لا تتأثر
        ->and(ServiceRequest::count())->toBe(1);
});
