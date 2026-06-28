<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('اسم الخدمة')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->label('الرابط (slug)')->required(),
                        TextInput::make('key')->label('المفتاح')->required()->helperText('ecommerce / tech_solution / training'),
                        TextInput::make('icon')->label('الأيقونة (إيموجي)')->placeholder('🛒'),
                        ColorPicker::make('color')->label('لون الخدمة'),
                        TextInput::make('tagline')->label('الشعار الفرعي'),
                        Textarea::make('summary')->label('وصف مختصر')->rows(2)->columnSpanFull(),
                        Toggle::make('is_active')->label('مُفعّلة')->default(true),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),

                Section::make('محتوى صفحة الخدمة')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('hero_title')->label('عنوان الواجهة'),
                        TextInput::make('cta_label')->label('نص زر الإجراء'),
                        Textarea::make('hero_subtitle')->label('وصف الواجهة')->rows(2)->columnSpanFull(),
                        Textarea::make('description')->label('الوصف التفصيلي')->rows(4)->columnSpanFull(),
                    ]),

                Section::make('المميزات')
                    ->collapsible()
                    ->schema([
                        Repeater::make('features')->label('المميزات')->schema([
                            TextInput::make('icon')->label('أيقونة')->placeholder('✦'),
                            TextInput::make('title.ar')->label('العنوان (ع)'),
                            TextInput::make('title.en')->label('Title (EN)'),
                            Textarea::make('desc.ar')->label('الوصف (ع)')->rows(2),
                            Textarea::make('desc.en')->label('Description (EN)')->rows(2),
                        ])->columns(2)->defaultItems(0)->reorderable()->collapsed(),
                    ]),

                Section::make('الباقات والأسعار')
                    ->collapsible()
                    ->schema([
                        Repeater::make('pricing')->label('الباقات')->schema([
                            TextInput::make('name.ar')->label('اسم الباقة (ع)'),
                            TextInput::make('name.en')->label('Plan name (EN)'),
                            TextInput::make('price.ar')->label('السعر (ع)')->placeholder('١٬٤٩٩ ج.م'),
                            TextInput::make('price.en')->label('Price (EN)')->placeholder('EGP 1,499'),
                            TextInput::make('period.ar')->label('المدة (ع)')->placeholder('/ شهرياً'),
                            TextInput::make('period.en')->label('Period (EN)')->placeholder('/ month'),
                            TagsInput::make('features.ar')->label('المزايا (ع)')->columnSpanFull(),
                            TagsInput::make('features.en')->label('Features (EN)')->columnSpanFull(),
                            Toggle::make('featured')->label('الأكثر طلباً'),
                        ])->columns(2)->defaultItems(0)->reorderable()->collapsed(),
                    ]),

                Section::make('الأسئلة الشائعة')
                    ->collapsible()
                    ->schema([
                        Repeater::make('faqs')->label('الأسئلة')->schema([
                            TextInput::make('q.ar')->label('السؤال (ع)'),
                            TextInput::make('q.en')->label('Question (EN)'),
                            Textarea::make('a.ar')->label('الإجابة (ع)')->rows(2),
                            Textarea::make('a.en')->label('Answer (EN)')->rows(2),
                        ])->columns(2)->defaultItems(0)->reorderable()->collapsed(),
                    ]),

                Section::make('حقول الفورم الخاصة بالخدمة')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('form_fields')->label('الحقول')->schema([
                            TextInput::make('name')->label('المعرّف (إنجليزي)'),
                            TextInput::make('label')->label('التسمية'),
                            Select::make('type')->label('النوع')->options([
                                'text' => 'نص', 'textarea' => 'نص طويل', 'select' => 'قائمة',
                            ])->default('text'),
                            TagsInput::make('options')->label('الخيارات (للقائمة)')->columnSpanFull(),
                            Toggle::make('full')->label('عرض كامل'),
                        ])->columns(3)->defaultItems(0)->collapsed(),
                    ]),

                Section::make('تحسين محركات البحث (SEO)')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('عنوان SEO')->maxLength(70),
                        TextInput::make('og_image')->label('صورة المشاركة (OG)'),
                        Textarea::make('meta_description')->label('وصف SEO')->rows(2)->maxLength(170)->columnSpanFull(),
                    ]),

                Section::make('🌐 المحتوى بالإنجليزية (English)')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('name_en')->label('Name'),
                        TextInput::make('tagline_en')->label('Tagline'),
                        Textarea::make('summary_en')->label('Summary')->rows(2)->columnSpanFull(),
                        TextInput::make('hero_title_en')->label('Hero title'),
                        TextInput::make('cta_label_en')->label('CTA label'),
                        Textarea::make('hero_subtitle_en')->label('Hero subtitle')->rows(2)->columnSpanFull(),
                        Textarea::make('description_en')->label('Description')->rows(4)->columnSpanFull(),
                        TextInput::make('meta_title_en')->label('SEO title'),
                        Textarea::make('meta_description_en')->label('SEO description')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
