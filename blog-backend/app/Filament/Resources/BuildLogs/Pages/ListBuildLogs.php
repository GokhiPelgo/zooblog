<?php

namespace App\Filament\Resources\BuildLogs\Pages;

use App\Filament\Resources\BuildLogs\BuildLogResource;
use Filament\Resources\Pages\ListRecords;

class ListBuildLogs extends ListRecords
{
    protected static string $resource = BuildLogResource::class;

    // Solo lectura: sin botón de crear.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
