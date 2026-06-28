<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الصفحة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('عنوان الصفحة')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, $context) => $context === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                        TextInput::make('slug')->label('الرابط (slug)')->required(),
                        Select::make('status')->label('الحالة')->options([
                            'draft' => 'مسودة', 'published' => 'منشورة',
                        ])->default('draft')->required(),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                        Textarea::make('excerpt')->label('وصف مختصر')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('محتوى الصفحة (Page Builder)')
                    ->schema([
                        Builder::make('content')->label('الأقسام')->blocks(self::blocks())->collapsible()->blockNumbers(false),
                    ]),

                Section::make('تحسين محركات البحث (SEO)')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('عنوان SEO')->maxLength(70),
                        TextInput::make('meta_keywords')->label('الكلمات المفتاحية'),
                        Textarea::make('meta_description')->label('وصف SEO')->rows(2)->maxLength(170)->columnSpanFull(),
                        TextInput::make('og_image')->label('صورة المشاركة (OG)'),
                        TextInput::make('schema_type')->label('نوع Schema')->default('WebPage'),
                        Toggle::make('noindex')->label('منع الأرشفة (noindex)'),
                    ]),

                Section::make('🌐 المحتوى بالإنجليزية (English)')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('title_en')->label('Title'),
                        Textarea::make('excerpt_en')->label('Excerpt')->rows(2)->columnSpanFull(),
                        TextInput::make('meta_title_en')->label('SEO title'),
                        Textarea::make('meta_description_en')->label('SEO description')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function blocks(): array
    {
        return [
            Block::make('hero')->label('قسم رئيسي (Hero)')->icon('heroicon-o-megaphone')->schema([
                TextInput::make('title')->label('العنوان'),
                Textarea::make('subtitle')->label('الوصف')->rows(2),
                TextInput::make('cta_label')->label('نص الزر'),
                TextInput::make('cta_url')->label('رابط الزر'),
            ]),
            Block::make('richtext')->label('نص منسّق')->icon('heroicon-o-document-text')->schema([
                RichEditor::make('html')->label('المحتوى'),
            ]),
            Block::make('features')->label('مميزات (شبكة)')->icon('heroicon-o-squares-2x2')->schema([
                TextInput::make('title')->label('عنوان القسم'),
                Repeater::make('items')->label('العناصر')->schema([
                    TextInput::make('icon')->label('أيقونة')->placeholder('✦'),
                    TextInput::make('title')->label('العنوان'),
                    Textarea::make('desc')->label('الوصف')->rows(2),
                ])->columns(3)->defaultItems(3)->collapsed(),
            ]),
            Block::make('stats')->label('إحصائيات')->icon('heroicon-o-chart-bar')->schema([
                Repeater::make('items')->label('الأرقام')->schema([
                    TextInput::make('value')->label('القيمة'),
                    TextInput::make('label')->label('الوصف'),
                ])->columns(2)->defaultItems(4),
            ]),
            Block::make('cta')->label('دعوة لإجراء (CTA)')->icon('heroicon-o-cursor-arrow-rays')->schema([
                TextInput::make('title')->label('العنوان'),
                Textarea::make('subtitle')->label('الوصف')->rows(2),
                TextInput::make('button_label')->label('نص الزر'),
                TextInput::make('button_url')->label('رابط الزر'),
            ]),
            Block::make('faq')->label('أسئلة شائعة')->icon('heroicon-o-question-mark-circle')->schema([
                TextInput::make('title')->label('عنوان القسم'),
                Repeater::make('items')->label('الأسئلة')->schema([
                    TextInput::make('q')->label('السؤال'),
                    Textarea::make('a')->label('الإجابة')->rows(2),
                ])->defaultItems(2)->collapsed(),
            ]),
        ];
    }
}
