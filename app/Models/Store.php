<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Store extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'tagline', 'description', 'meta_title', 'meta_description'];

    protected $fillable = [
        'user_id', 'name', 'slug', 'tagline', 'description', 'logo_path', 'cover_path',
        'primary_color', 'theme', 'phone', 'whatsapp', 'address', 'currency',
        'plan', 'status', 'settings', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(StoreOrder::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }
}
