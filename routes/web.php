<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// الخدمات الثلاث — صفحة تفصيلية + فورم منفصل لكل خدمة
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/services/{service:slug}/request', [ServiceController::class, 'submit'])
    ->middleware('throttle:10,1')->name('services.submit');

// تواصل
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])
    ->middleware('throttle:10,1')->name('contact.submit');

// باني المتاجر — توجيه بالمسار (مناسب للاستضافة المشتركة)
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::scopeBindings()->group(function () {
    Route::get('/store/{store:slug}', [StoreController::class, 'show'])->name('store.show');
    Route::get('/store/{store:slug}/product/{product:slug}', [StoreController::class, 'product'])->name('store.product');
    Route::post('/store/{store:slug}/product/{product:slug}/order', [StoreController::class, 'order'])
        ->middleware('throttle:15,1')->name('store.order');
});

// نموذج عرض السعر التفاعلي للمتاجر الإلكترونية + روابط مخصّصة لكل عميل
Route::get('/quote', [QuoteController::class, 'show'])->name('quote');
Route::post('/quote', [QuoteController::class, 'submit'])
    ->middleware('throttle:10,1')->name('quote.submit');
Route::get('/quote/document/{serviceRequest}', [QuoteController::class, 'documentSigned'])
    ->middleware('signed')->name('quote.document.signed');
Route::get('/quote/proposal/{serviceRequest}', [QuoteController::class, 'proposalSigned'])
    ->middleware('signed')->name('quote.proposal.signed');
Route::get('/quote/{invite}', [QuoteController::class, 'show'])->name('quote.invite');
Route::post('/quote/{invite}', [QuoteController::class, 'submit'])
    ->middleware('throttle:10,1')->name('quote.invite.submit');
Route::get('/quote/{invite}/document', [QuoteController::class, 'document'])->name('quote.document');
Route::get('/quote/{invite}/proposal', [QuoteController::class, 'proposal'])->name('quote.proposal');
// اختصار شخصي يُشارك مع العميلة مباشرة
Route::redirect('/hajar-salama', '/quote/hajar-salama');

// SEO التقني
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// صفحات الـ CMS الديناميكية (يجب أن تكون الأخيرة)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-_]+')
    ->name('page.show');
