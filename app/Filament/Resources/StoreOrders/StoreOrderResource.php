<?php

namespace App\Filament\Resources\StoreOrders;

use App\Filament\Resources\StoreOrders\Pages\CreateStoreOrder;
use App\Filament\Resources\StoreOrders\Pages\EditStoreOrder;
use App\Filament\Resources\StoreOrders\Pages\ListStoreOrders;
use App\Filament\Resources\StoreOrders\Schemas\StoreOrderForm;
use App\Filament\Resources\StoreOrders\Tables\StoreOrdersTable;
use App\Models\StoreOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreOrderResource extends Resource
{
    protected static ?string $model = StoreOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|\UnitEnum|null $navigationGroup = 'باني المتاجر';

    protected static ?string $navigationLabel = 'طلبات المتاجر';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'طلبات المتاجر';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return StoreOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreOrders::route('/'),
            'create' => CreateStoreOrder::route('/create'),
            'edit' => EditStoreOrder::route('/{record}/edit'),
        ];
    }
}
