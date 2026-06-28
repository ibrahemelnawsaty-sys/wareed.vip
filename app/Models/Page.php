<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'excerpt', 'meta_title', 'meta_description'];

    protected $fillable = [
        'slug', 'title', 'locale', 'status', 'excerpt', 'content',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image',
        'schema_type', 'noindex', 'is_system', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'noindex' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
