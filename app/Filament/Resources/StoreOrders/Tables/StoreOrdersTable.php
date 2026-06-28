<?php

namespace App\Filament\Resources\StoreOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StoreOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('رقم الطلب')->searchable()->weight('bold'),
                TextColumn::make('store.name')->label('المتجر')->searchable()->color('gray'),
                TextColumn::make('customer_name')->label('العميل')->searchable(),
                TextColumn::make('customer_phone')->label('الموبايل')->copyable()->toggleable(),
                TextColumn::make('total')->label('الإجمالي')->sortable()
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 0).' ج.م'),
                TextColumn::make('payment_method')->label('الدفع')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'cod' ? 'عند الاستلام' : 'إلكتروني'),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'جديد', 'confirmed' => 'مؤكّد', 'shipped' => 'تم الشحن',
                        'delivered' => 'تم التسليم', 'cancelled' => 'ملغي', default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'gray', 'confirmed' => 'info', 'shipped' => 'warning',
                        'delivered' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options([
                    'new' => 'جديد', 'confirmed' => 'مؤكّد', 'shipped' => 'تم الشحن',
                    'delivered' => 'تم التسليم', 'cancelled' => 'ملغي',
                ]),
                SelectFilter::make('store_id')->label('المتجر')->relationship('store', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
