<?php

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
