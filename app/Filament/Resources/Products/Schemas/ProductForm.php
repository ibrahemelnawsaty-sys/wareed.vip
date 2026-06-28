<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المنتج')
                    ->columns(2)
                    ->schema([
                        Select::make('store_id')->label('المتجر')->relationship('store', 'name')->required()->searchable(),
                        Select::make('category_id')->label('التصنيف')->relationship('category', 'name')->searchable(),
                        TextInput::make('name')->label('اسم المنتج')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->label('الرابط')->required(),
                        Textarea::make('description')->label('الوصف')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('السعر والمخزون')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')->label('السعر (ج.م)')->numeric()->required()->suffix('ج.م')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('compare_price')->label('السعر قبل الخصم (ج.م)')->numeric()->suffix('ج.م')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                        TextInput::make('sku')->label('SKU'),
                        TextInput::make('stock')->label('المخزون')->numeric()->default(0),
                        Toggle::make('track_stock')->label('تتبّع المخزون'),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),

                Section::make('الصور والحالة')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('images')->label('صور المنتج')->image()->multiple()->reorderable()
                            ->disk('public')->directory('products')->columnSpanFull(),
                        Toggle::make('is_active')->label('مُفعّل')->default(true),
                        Toggle::make('is_featured')->label('مميّز'),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('عنوان SEO'),
                        Textarea::make('meta_description')->label('وصف SEO')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('🌐 المحتوى بالإنجليزية (English)')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('name_en')->label('Name'),
                        Textarea::make('description_en')->label('Description')->rows(3)->columnSpanFull(),
                        TextInput::make('meta_title_en')->label('SEO title'),
                        Textarea::make('meta_description_en')->label('SEO description')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
