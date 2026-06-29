<?php

namespace App\Observers;

use App\Mail\ServiceRequestConfirmation;
use App\Mail\ServiceRequestReceived;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Mail;

class ServiceRequestObserver
{
    public function created(ServiceRequest $sr): void
    {
        // الطلب محفوظ في قاعدة البيانات بالفعل؛ الإيميل إشعار أفضل-جهد.
        // عند فشل الإرسال نُبلّغ (report) ولا نكسر تجربة المستخدم ولا نبتلع الخطأ صامتين (دستور §3).
        try {
            $admin = setting('contact_email', 'info@wareed.vip');

            Mail::to($admin)->send(new ServiceRequestReceived($sr));

            if (filter_var($sr->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($sr->email)->send(new ServiceRequestConfirmation($sr));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
