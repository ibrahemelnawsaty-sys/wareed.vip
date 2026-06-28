<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\TranslatesFields;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use TranslatesFields;

    protected static string $resource = ProductResource::class;
}
