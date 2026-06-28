<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        $services = Service::active()->orderBy('sort_order')->get();

        return view('home', ['services' => $services]);
    }

    public function contact()
    {
        $services = Service::active()->orderBy('sort_order')->get();

        return view('contact', [
            'services' => $services,
            'seo' => [
                'title' => 'تواصل معنا',
                'description' => 'تواصل مع فريق وريد لبدء مشروعك التقني — متاجر، حلول، أو تدريب.',
            ],
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
        ]);

        ServiceRequest::create([
            'service_type' => $data['service_type'] ?? 'general',
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'company' => $data['company'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'new',
            'source' => 'contact_page',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'تم استلام رسالتك بنجاح! سيتواصل معك فريق وريد قريباً.');
    }

    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'seo' => [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description ?: $page->excerpt,
                'image' => $page->og_image,
                'noindex' => $page->noindex,
                'keywords' => $page->meta_keywords,
                'schema_type' => $page->schema_type,
            ],
        ]);
    }
}
