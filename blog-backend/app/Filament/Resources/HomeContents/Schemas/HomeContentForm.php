<?php

namespace App\Filament\Resources\HomeContents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class HomeContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Idiomas')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Español')
                            ->schema(self::textFields('es')),
                        Tab::make('English')
                            ->schema(self::textFields('en')),
                    ]),

                Section::make('Imágenes del collage')
                    ->description('Compartidas para ambos idiomas. Si dejas una vacía, se usa la imagen por defecto del sitio. El texto alternativo (alt) se edita en cada pestaña de idioma.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image1')
                            ->label('Imagen 1 (principal, vertical)')
                            ->image()
                            ->disk(config('filesystems.tutorials_disk'))
                            ->directory('home'),
                        FileUpload::make('image2')
                            ->label('Imagen 2')
                            ->image()
                            ->disk(config('filesystems.tutorials_disk'))
                            ->directory('home'),
                        FileUpload::make('image3')
                            ->label('Imagen 3')
                            ->image()
                            ->disk(config('filesystems.tutorials_disk'))
                            ->directory('home'),
                        FileUpload::make('image4')
                            ->label('Imagen 4 (ancha, abajo)')
                            ->image()
                            ->disk(config('filesystems.tutorials_disk'))
                            ->directory('home'),
                    ]),
            ]);
    }

    /**
     * Campos de texto para un idioma ($lang = 'es' | 'en').
     */
    protected static function textFields(string $lang): array
    {
        return [
            TextInput::make("badge_$lang")
                ->label('Etiqueta superior (badge)')
                ->helperText('Texto pequeño arriba del título.')
                ->maxLength(255),
            TextInput::make("title_$lang")
                ->label('Título principal')
                ->maxLength(255),
            Textarea::make("subtitle_$lang")
                ->label('Subtítulo / párrafo')
                ->rows(3),

            TextInput::make("primary_label_$lang")
                ->label('Botón 1 — texto')
                ->maxLength(255),
            TextInput::make("primary_url_$lang")
                ->label('Botón 1 — enlace')
                ->helperText('Ej: /'.$lang.'/blog')
                ->maxLength(255),
            TextInput::make("secondary_label_$lang")
                ->label('Botón 2 — texto')
                ->maxLength(255),
            TextInput::make("secondary_url_$lang")
                ->label('Botón 2 — enlace')
                ->helperText('Ej: /'.$lang.'/blog')
                ->maxLength(255),

            TextInput::make("image1_alt_$lang")
                ->label('Imagen 1 — texto alternativo (alt)')
                ->maxLength(255),
            TextInput::make("image2_alt_$lang")
                ->label('Imagen 2 — texto alternativo (alt)')
                ->maxLength(255),
            TextInput::make("image3_alt_$lang")
                ->label('Imagen 3 — texto alternativo (alt)')
                ->maxLength(255),
            TextInput::make("image4_alt_$lang")
                ->label('Imagen 4 — texto alternativo (alt)')
                ->maxLength(255),
        ];
    }
}
