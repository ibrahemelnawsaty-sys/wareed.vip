<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('المتجر')->searchable()->weight('bold'),
                TextColumn::make('slug')->label('الرابط')->searchable()->color('gray'),
                TextColumn::make('plan')->label('الباقة')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'free' => 'مجانية', 'growth' => 'النمو', 'pro' => 'الاحترافية', default => $state,
                    }),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'active' ? 'نشط' : 'موقوف')
                    ->color(fn (string $state) => $state === 'active' ? 'success' : 'danger'),
                TextColumn::make('products_count')->label('المنتجات')->counts('products')->badge(),
                TextColumn::make('orders_count')->label('الطلبات')->counts('orders')->badge()->color('warning'),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options([
                    'active' => 'نشط', 'suspended' => 'موقوف',
                ]),
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
