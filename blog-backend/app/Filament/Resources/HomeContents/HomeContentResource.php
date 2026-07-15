<?php

namespace App\Filament\Resources\HomeContents;

use App\Filament\Resources\HomeContents\Pages\EditHomeContent;
use App\Filament\Resources\HomeContents\Pages\ListHomeContents;
use App\Filament\Resources\HomeContents\Schemas\HomeContentForm;
use App\Filament\Resources\HomeContents\Tables\HomeContentsTable;
use App\Models\HomeContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HomeContentResource extends Resource
{
    protected static ?string $model = HomeContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $recordTitleAttribute = 'title_es';

    protected static ?string $navigationLabel = 'Inicio (portada)';

    protected static ?int $navigationSort = -1;

    // El menú lleva directo a editar la portada (registro único, id=1).
    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => 1]);
    }

    public static function form(Schema $schema): Schema
    {
        return HomeContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomeContentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // Solo listar y editar: los dos idiomas ya existen (seeder), no se crean ni borran.
        return [
            'index' => ListHomeContents::route('/'),
            'edit' => EditHomeContent::route('/{record}/edit'),
        ];
    }
}
