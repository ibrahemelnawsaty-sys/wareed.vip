<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المتجر')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('اسم المتجر')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->label('الرابط (slug)')->required()
                            ->helperText('يظهر في: /store/slug'),
                        TextInput::make('tagline')->label('الشعار الفرعي'),
                        Select::make('user_id')->label('المالك')->relationship('owner', 'name')->searchable(),
                        Textarea::make('description')->label('وصف المتجر')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('المظهر')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('primary_color')->label('اللون الأساسي')->default('#00C896'),
                        TextInput::make('theme')->label('القالب')->default('aurora'),
                        FileUpload::make('logo_path')->label('الشعار')->image()->disk('public')->directory('stores'),
                        FileUpload::make('cover_path')->label('صورة الغلاف')->image()->disk('public')->directory('stores'),
                    ]),

                Section::make('التواصل والخطة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->label('الهاتف')->tel(),
                        TextInput::make('whatsapp')->label('واتساب (أرقام فقط)'),
                        TextInput::make('address')->label('العنوان'),
                        TextInput::make('currency')->label('العملة')->default('EGP'),
                        Select::make('plan')->label('الباقة')->options([
                            'free' => 'مجانية', 'growth' => 'النمو', 'pro' => 'الاحترافية',
                        ])->default('free'),
                        Select::make('status')->label('الحالة')->options([
                            'active' => 'نشط', 'suspended' => 'موقوف',
                        ])->default('active'),
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
                        TextInput::make('tagline_en')->label('Tagline'),
                        Textarea::make('description_en')->label('Description')->rows(3)->columnSpanFull(),
                        TextInput::make('meta_title_en')->label('SEO title'),
                        Textarea::make('meta_description_en')->label('SEO description')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
