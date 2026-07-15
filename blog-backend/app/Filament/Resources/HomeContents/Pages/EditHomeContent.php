<?php

namespace App\Filament\Resources\HomeContents\Pages;

use App\Filament\Resources\HomeContents\HomeContentResource;
use Filament\Resources\Pages\EditRecord;

class EditHomeContent extends EditRecord
{
    protected static string $resource = HomeContentResource::class;

    // Sin botón de "borrar": la portada no se elimina, solo se edita.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
