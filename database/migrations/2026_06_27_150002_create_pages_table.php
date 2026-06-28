<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');                       // قابل للترجمة
            $table->string('locale', 5)->default('ar');
            $table->string('status')->default('draft'); // draft, published
            $table->json('excerpt')->nullable();        // قابل للترجمة
            $table->json('content')->nullable();        // Page Builder blocks
            // SEO — قابل للتحرير من الأدمن (دستور §11) + قابل للترجمة
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('schema_type')->default('WebPage');
            $table->boolean('noindex')->default(false);
            $table->boolean('is_system')->default(false); // صفحات محميّة من الحذف
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
