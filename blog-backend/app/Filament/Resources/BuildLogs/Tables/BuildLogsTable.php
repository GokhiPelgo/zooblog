<?php

namespace App\Filament\Resources\BuildLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuildLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('started_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('Usuario')
                    ->default('—')
                    ->searchable(),
                TextColumn::make('mode')
                    ->label('Modo')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'running' => 'En proceso',
                        'success' => 'Éxito',
                        'error'   => 'Error',
                        default   => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error'   => 'danger',
                        default   => 'warning',
                    }),
                TextColumn::make('finished_at')
                    ->label('Terminó')
                    ->dateTime('d/m/Y H:i:s')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Detalle')
                    ->limit(60)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—'),
            ]);
    }
}
