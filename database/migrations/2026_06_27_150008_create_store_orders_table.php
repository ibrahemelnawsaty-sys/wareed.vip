<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('governorate')->nullable();
            // لقطة العناصر وقت الطلب (دستور §3: Snapshot المحاسبي)
            $table->json('items');
            $table->unsignedBigInteger('subtotal')->default(0);  // قروش
            $table->unsignedBigInteger('shipping')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('payment_method')->default('cod');     // cod, online
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('new');             // new, confirmed, shipped, delivered, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_orders');
    }
};
