<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('المنتج')->searchable()->weight('bold'),
                TextColumn::make('store.name')->label('المتجر')->searchable()->color('gray'),
                TextColumn::make('category.name')->label('التصنيف')->toggleable(),
                TextColumn::make('price')->label('السعر')->sortable()
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 0).' ج.م'),
                TextColumn::make('stock')->label('المخزون')->numeric()->sortable(),
                IconColumn::make('is_active')->label('مفعّل')->boolean(),
                IconColumn::make('is_featured')->label('مميّز')->boolean()->toggleable(),
            ])
            ->filters([
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
