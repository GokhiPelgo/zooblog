<?php

namespace App\Filament\Resources\HomeContents\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title_es')
                    ->label('Título (ES)'),
                TextColumn::make('title_en')
                    ->label('Título (EN)'),
                TextColumn::make('updated_at')
                    ->label('Última edición')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
