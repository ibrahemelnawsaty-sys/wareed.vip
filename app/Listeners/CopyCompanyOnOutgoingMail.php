<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

/**
 * نسخة CC إلى بريد وريد (إعداد contact_email) على كل رسالة صادرة للعملاء، فيبقى
 * سجلّ المراسلات كاملاً في صندوق الشركة. الرسائل الموجّهة أصلاً إلى بريد وريد
 * (إشعارات الفريق والرسائل التجريبية) لا تُنسخ إليه مرة أخرى.
 *
 * يُكتشف تلقائياً من مجلد Listeners ويعمل على كل ما يمرّ بالمُرسِل: رسائل المراحل
 * وعروض الأسعار والعقود ورسائل الاستلام وطلبات المتاجر.
 */
class CopyCompanyOnOutgoingMail
{
    public function handle(MessageSending $event): void
    {
        $company = strtolower(trim((string) setting('contact_email', 'info@wareed.vip')));

        if (! filter_var($company, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $message = $event->message;

        foreach (array_merge($message->getTo(), $message->getCc(), $message->getBcc()) as $address) {
            if (strtolower($address->getAddress()) === $company) {
                return;
            }
        }

        $message->addCc(new Address($company, 'وريد لتقنية المعلومات'));
    }
}
