<?php

namespace App\Filament\Resources\ServiceRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات العميل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('الاسم')->required(),
                        TextInput::make('phone')->label('الموبايل / واتساب')->tel()->required(),
                        TextInput::make('email')->label('البريد الإلكتروني')->email(),
                        TextInput::make('company')->label('الشركة / النشاط'),
                    ]),

                Section::make('تفاصيل الطلب')
                    ->columns(2)
                    ->schema([
                        Select::make('service_id')->label('الخدمة')->relationship('service', 'name'),
                        Select::make('service_type')->label('نوع الخدمة')->options([
                            'ecommerce' => 'المتاجر الإلكترونية',
                            'tech_solution' => 'الحلول التقنية',
                            'training' => 'التدريب التقني',
                            'general' => 'استفسار عام',
                        ])->required(),
                        TextInput::make('budget')->label('الميزانية'),
                        TextInput::make('source')->label('المصدر')->disabled(),
                        Textarea::make('message')->label('الرسالة')->columnSpanFull()->rows(3),
                        KeyValue::make('payload')->label('حقول إضافية')->columnSpanFull(),
                    ]),

                Section::make('المتابعة')
                    ->columns(2)
                    ->schema([
                        Select::make('status')->label('الحالة')->options([
                            'new' => 'جديد',
                            'contacted' => 'تم التواصل',
                            'qualified' => 'مؤهّل',
                            'proposal' => 'عرض سعر',
                            'won' => 'تم الكسب',
                            'lost' => 'مفقود',
                        ])->default('new')->required(),
                        Select::make('assigned_to')->label('مسؤول المتابعة')->relationship('assignee', 'name')->searchable(),
                        DateTimePicker::make('contacted_at')->label('تاريخ التواصل'),
                    ]),
            ]);
    }
}
