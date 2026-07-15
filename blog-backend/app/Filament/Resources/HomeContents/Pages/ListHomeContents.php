<?php

namespace App\Filament\Resources\HomeContents\Pages;

use App\Filament\Resources\HomeContents\HomeContentResource;
use Filament\Resources\Pages\ListRecords;

class ListHomeContents extends ListRecords
{
    protected static string $resource = HomeContentResource::class;

    // Sin botón de "crear": los dos idiomas ya existen.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
