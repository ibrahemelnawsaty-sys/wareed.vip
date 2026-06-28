<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // متاجر المستأجرين — قاعدة واحدة + عزل بالـ store_id (مناسب للاستضافة المشتركة)
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('name');                 // قابل للترجمة
            $table->string('slug')->unique();
            $table->json('tagline')->nullable();
            $table->json('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('primary_color')->default('#00C896');
            $table->string('theme')->default('aurora');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->string('currency', 8)->default('EGP');
            $table->string('plan')->default('free');     // free, growth, pro
            $table->string('status')->default('active');  // active, suspended
            $table->json('settings')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
