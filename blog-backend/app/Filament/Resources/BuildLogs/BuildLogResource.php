<?php

namespace App\Filament\Resources\BuildLogs;

use App\Filament\Resources\BuildLogs\Pages\ListBuildLogs;
use App\Filament\Resources\BuildLogs\Tables\BuildLogsTable;
use App\Models\BuildLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BuildLogResource extends Resource
{
    protected static ?string $model = BuildLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Registro de publicaciones';

    protected static ?string $modelLabel = 'publicación';

    protected static ?string $pluralModelLabel = 'publicaciones';

    protected static ?int $navigationSort = 10;

    // Solo lectura: no se crea ni se edita a mano.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return BuildLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuildLogs::route('/'),
        ];
    }
}
