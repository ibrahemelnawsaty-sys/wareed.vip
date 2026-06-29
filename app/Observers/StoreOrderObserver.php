<?php

namespace App\Observers;

use App\Mail\StoreOrderReceived;
use App\Models\StoreOrder;
use Illuminate\Support\Facades\Mail;

class StoreOrderObserver
{
    public function created(StoreOrder $order): void
    {
        try {
            $admin = setting('contact_email', 'info@wareed.vip');

            Mail::to($admin)->send(new StoreOrderReceived($order));

            // إشعار صاحب المتجر أيضاً إن كان له بريد مختلف
            $owner = $order->store?->owner;
            if ($owner && filter_var($owner->email, FILTER_VALIDATE_EMAIL) && $owner->email !== $admin) {
                Mail::to($owner->email)->send(new StoreOrderReceived($order));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
