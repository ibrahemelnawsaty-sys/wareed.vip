<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Concerns\TranslatesFields;
use App\Filament\Resources\Stores\StoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStore extends CreateRecord
{
    use TranslatesFields;

    protected static string $resource = StoreResource::class;
}
