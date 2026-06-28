<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function show(Service $service)
    {
        abort_unless($service->is_active, 404);

        return view('services.show', [
            'service' => $service,
            'seo' => [
                'title' => $service->meta_title ?: $service->name,
                'description' => $service->meta_description ?: $service->summary,
                'image' => $service->og_image,
                'og_type' => 'product',
                'schema_type' => 'Service',
            ],
        ]);
    }

    public function submit(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'budget' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:5000',
        ]);

        // الحقول الإضافية الخاصة بالخدمة (form_fields) تُحفظ في payload
        $payload = [];
        foreach ((array) $service->form_fields as $field) {
            $name = $field['name'] ?? null;
            if ($name && $request->filled("extra_$name")) {
                $payload[$name] = $request->input("extra_$name");
            }
        }

        ServiceRequest::create([
            'service_id' => $service->id,
            'service_type' => $service->key,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'company' => $data['company'] ?? null,
            'budget' => $data['budget'] ?? null,
            'message' => $data['message'] ?? null,
            'payload' => $payload ?: null,
            'status' => 'new',
            'source' => 'service_'.$service->key,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'تم استلام طلبك بنجاح! سيتواصل معك فريق وريد خلال ساعات.');
    }
}
