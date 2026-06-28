<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')->label('المتجر')->relationship('store', 'name')->required()->searchable(),
                TextInput::make('name')->label('اسم التصنيف (عربي)')->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state, language: null))),
                TextInput::make('name_en')->label('Category name (EN)'),
                TextInput::make('slug')->label('الرابط')->required(),
                TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                Toggle::make('is_active')->label('مُفعّل')->default(true),
            ]);
    }
}
