<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use App\Models\Store;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];
        $urls[] = ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => url('/stores'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly'];

        foreach (Service::active()->get() as $s) {
            $urls[] = ['loc' => url('/services/'.$s->slug), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $s->updated_at?->toAtomString()];
        }

        foreach (Page::published()->where('noindex', false)->get() as $p) {
            $urls[] = ['loc' => url('/'.$p->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $p->updated_at?->toAtomString()];
        }

        foreach (Store::active()->get() as $store) {
            $urls[] = ['loc' => url('/store/'.$store->slug), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $store->updated_at?->toAtomString()];
            // عزل المتاجر: نقرأ منتجات هذا المتجر عبر العلاقة
            foreach ($store->products()->where('is_active', true)->get() as $product) {
                $urls[] = ['loc' => url('/store/'.$store->slug.'/product/'.$product->slug), 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $product->updated_at?->toAtomString()];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (app()->environment('production')) {
            $lines[] = 'Allow: /';
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /login';
        } else {
            // دستور §11: غير الإنتاج لا يُؤرشَف أبداً
            $lines[] = 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }
}
