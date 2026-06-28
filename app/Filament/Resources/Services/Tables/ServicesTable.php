<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('icon')->label('')->size('lg'),
                TextColumn::make('name')->label('الخدمة')->searchable()->weight('bold'),
                TextColumn::make('key')->label('المفتاح')->badge()->color('gray'),
                TextColumn::make('tagline')->label('الشعار')->toggleable()->limit(40),
                IconColumn::make('is_active')->label('مفعّلة')->boolean(),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                TextColumn::make('requests_count')->label('الطلبات')->counts('requests')->badge()->color('success'),
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
