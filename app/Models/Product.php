<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use BelongsToStore, HasTranslations;

    public array $translatable = ['name', 'description', 'meta_title', 'meta_description'];

    protected $fillable = [
        'store_id', 'category_id', 'name', 'slug', 'description', 'price', 'compare_price',
        'sku', 'stock', 'track_stock', 'images', 'is_active', 'is_featured', 'sort_order',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_stock' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // المال مخزّن بالقروش (عدد صحيح) — دستور §3
    public function priceInEgp(): float
    {
        return $this->price / 100;
    }

    public function comparePriceInEgp(): ?float
    {
        return $this->compare_price ? $this->compare_price / 100 : null;
    }

    public function firstImageUrl(): ?string
    {
        $img = is_array($this->images) ? ($this->images[0] ?? null) : null;

        return $img ? asset('storage/'.$img) : null;
    }
}
