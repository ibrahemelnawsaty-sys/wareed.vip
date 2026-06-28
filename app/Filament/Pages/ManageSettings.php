<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'إدارة الموقع';

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?int $navigationSort = 9;

    public ?array $data = [];

    protected array $translatableKeys = [
        'site_name', 'site_tagline', 'site_description',
        'hero_title', 'hero_title_accent', 'hero_subtitle',
        'contact_address', 'seo_keywords',
    ];

    protected array $plainKeys = [
        'contact_phone', 'contact_email', 'contact_whatsapp',
        'social_facebook', 'social_instagram', 'social_linkedin', 'social_tiktok',
    ];

    public function getTitle(): string
    {
        return 'إعدادات الموقع';
    }

    public function mount(): void
    {
        $models = Setting::all()->keyBy('key');
        $state = [];

        foreach ($this->translatableKeys as $key) {
            $m = $models->get($key);
            $state[$key] = $m?->getTranslation('value', 'ar', false);
            $state[$key.'_en'] = $m?->getTranslation('value', 'en', false);
        }
        foreach ($this->plainKeys as $key) {
            $m = $models->get($key);
            $state[$key] = $m?->getTranslation('value', 'ar', false);
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('الهوية العامة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')->label('اسم الموقع'),
                        TextInput::make('site_name_en')->label('Site name (EN)'),
                        TextInput::make('site_tagline')->label('الشعار الفرعي'),
                        TextInput::make('site_tagline_en')->label('Tagline (EN)'),
                        Textarea::make('site_description')->label('وصف الموقع')->rows(2)->columnSpanFull(),
                        Textarea::make('site_description_en')->label('Site description (EN)')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('الواجهة الرئيسية (Hero)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hero_title')->label('العنوان الرئيسي'),
                        TextInput::make('hero_title_en')->label('Hero title (EN)'),
                        TextInput::make('hero_title_accent')->label('الجزء المميّز (ذهبي)'),
                        TextInput::make('hero_title_accent_en')->label('Accent part (EN)'),
                        Textarea::make('hero_subtitle')->label('الوصف')->rows(2)->columnSpanFull(),
                        Textarea::make('hero_subtitle_en')->label('Hero subtitle (EN)')->rows(2)->columnSpanFull(),
                    ]),
                Section::make('بيانات التواصل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_phone')->label('الهاتف'),
                        TextInput::make('contact_whatsapp')->label('واتساب (أرقام فقط)'),
                        TextInput::make('contact_email')->label('البريد الإلكتروني')->email(),
                        TextInput::make('contact_address')->label('العنوان'),
                        TextInput::make('contact_address_en')->label('Address (EN)'),
                    ]),
                Section::make('وسائل التواصل الاجتماعي')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('social_facebook')->label('فيسبوك')->url(),
                        TextInput::make('social_instagram')->label('إنستجرام')->url(),
                        TextInput::make('social_linkedin')->label('لينكدإن')->url(),
                        TextInput::make('social_tiktok')->label('تيك توك')->url(),
                    ]),
                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Textarea::make('seo_keywords')->label('الكلمات المفتاحية')->rows(2),
                        Textarea::make('seo_keywords_en')->label('Keywords (EN)')->rows(2),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($this->translatableKeys as $key) {
            Setting::set($key, [
                'ar' => $data[$key] ?? null,
                'en' => $data[$key.'_en'] ?? null,
            ]);
        }
        foreach ($this->plainKeys as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        Notification::make()->title('تم حفظ الإعدادات بنجاح')->success()->send();
    }
}
