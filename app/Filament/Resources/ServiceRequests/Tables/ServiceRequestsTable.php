<?php

namespace App\Filament\Resources\ServiceRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->weight('bold'),
                TextColumn::make('phone')->label('الموبايل')->searchable()->copyable(),
                TextColumn::make('service_type')->label('الخدمة')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ecommerce' => 'متاجر',
                        'tech_solution' => 'حلول',
                        'training' => 'تدريب',
                        default => 'عام',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ecommerce' => 'success',
                        'tech_solution' => 'warning',
                        'training' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'جديد',
                        'contacted' => 'تم التواصل',
                        'qualified' => 'مؤهّل',
                        'proposal' => 'عرض سعر',
                        'won' => 'تم الكسب',
                        'lost' => 'مفقود',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'contacted' => 'info',
                        'qualified' => 'warning',
                        'proposal' => 'primary',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('company')->label('الشركة')->searchable()->toggleable(),
                TextColumn::make('assignee.name')->label('المسؤول')->toggleable(),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('service_type')->label('الخدمة')->options([
                    'ecommerce' => 'المتاجر الإلكترونية',
                    'tech_solution' => 'الحلول التقنية',
                    'training' => 'التدريب التقني',
                    'general' => 'استفسار عام',
                ]),
                SelectFilter::make('status')->label('الحالة')->options([
                    'new' => 'جديد',
                    'contacted' => 'تم التواصل',
                    'qualified' => 'مؤهّل',
                    'proposal' => 'عرض سعر',
                    'won' => 'تم الكسب',
                    'lost' => 'مفقود',
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
