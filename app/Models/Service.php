<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasTranslations;

    public array $translatable = [
        'name', 'tagline', 'summary', 'description',
        'hero_title', 'hero_subtitle', 'cta_label',
        'meta_title', 'meta_description',
    ];

    protected $fillable = [
        'key', 'slug', 'name', 'tagline', 'icon', 'color', 'summary', 'description',
        'hero_title', 'hero_subtitle', 'content', 'features', 'pricing', 'faqs',
        'form_fields', 'cta_label', 'meta_title', 'meta_description', 'og_image',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'features' => 'array',
            'pricing' => 'array',
            'faqs' => 'array',
            'form_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
