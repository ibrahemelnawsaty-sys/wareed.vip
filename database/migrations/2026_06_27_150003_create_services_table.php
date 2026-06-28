<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // ecommerce, tech_solution, training
            $table->string('slug')->unique();
            // حقول قابلة للترجمة (JSON: {"ar":..,"en":..}) — spatie/laravel-translatable
            $table->json('name');
            $table->json('tagline')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#C9A24B');
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->json('hero_title')->nullable();
            $table->json('hero_subtitle')->nullable();
            $table->json('content')->nullable();      // Page Builder blocks لصفحة الخدمة
            $table->json('features')->nullable();      // [{title, desc, icon}]
            $table->json('pricing')->nullable();       // [{name, price, period, features[], featured}]
            $table->json('faqs')->nullable();          // [{q, a}]
            $table->json('form_fields')->nullable();   // حقول الفورم الخاصة بالخدمة
            $table->json('cta_label')->nullable();
            // SEO (قابل للترجمة)
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
