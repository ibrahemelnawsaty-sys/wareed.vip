<?php

namespace App\Filament\Resources\StoreOrders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات العميل')
                    ->columns(2)
                    ->schema([
                        Select::make('store_id')->label('المتجر')->relationship('store', 'name')->required()->searchable(),
                        TextInput::make('order_number')->label('رقم الطلب')->required()->disabled()->dehydrated(),
                        TextInput::make('customer_name')->label('اسم العميل')->required(),
                        TextInput::make('customer_phone')->label('الموبايل')->tel()->required(),
                        TextInput::make('customer_email')->label('البريد الإلكتروني')->email(),
                        TextInput::make('governorate')->label('المحافظة'),
                        Textarea::make('shipping_address')->label('العنوان')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('تفاصيل الطلب والمبالغ')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')->label('الإجمالي الفرعي (ج.م)')->numeric()->suffix('ج.م')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('shipping')->label('الشحن (ج.م)')->numeric()->suffix('ج.م')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        TextInput::make('total')->label('الإجمالي (ج.م)')->numeric()->suffix('ج.م')
                            ->formatStateUsing(fn ($state) => $state !== null ? $state / 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                        Textarea::make('items')->label('المنتجات (لقطة)')->rows(4)->columnSpanFull()->disabled()->dehydrated(false)
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string) $state),
                    ]),

                Section::make('الحالة')
                    ->columns(3)
                    ->schema([
                        Select::make('status')->label('حالة الطلب')->options([
                            'new' => 'جديد', 'confirmed' => 'مؤكّد', 'shipped' => 'تم الشحن',
                            'delivered' => 'تم التسليم', 'cancelled' => 'ملغي',
                        ])->default('new')->required(),
                        Select::make('payment_method')->label('طريقة الدفع')->options([
                            'cod' => 'عند الاستلام', 'online' => 'إلكتروني',
                        ])->default('cod'),
                        Select::make('payment_status')->label('حالة الدفع')->options([
                            'pending' => 'معلّق', 'paid' => 'مدفوع', 'refunded' => 'مُسترجع',
                        ])->default('pending'),
                        Textarea::make('notes')->label('ملاحظات')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }
}
