<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\TranslatesFields;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use TranslatesFields;

    protected static string $resource = PageResource::class;
}
