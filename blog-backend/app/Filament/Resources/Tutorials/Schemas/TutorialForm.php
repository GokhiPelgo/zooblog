<?php

namespace App\Filament\Resources\Tutorials\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TutorialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->rule('regex:/^[a-z0-9-]+$/')
                    ->validationMessages([
                        'regex' => 'El slug solo acepta minúsculas, números y guiones — nada de espacios, acentos ni signos como ?¿.',
                    ])
                    ->helperText('Solo minúsculas, números y guiones. Ej: como-cuidar-un-perro'),
                TextInput::make('translation_key')
                    ->label('Clave de traducción')
                    ->helperText('Usa el MISMO valor en las dos versiones (es/en) para enlazarlas al cambiar de idioma. Ej: cuidar-perro. Déjalo vacío si el tutorial es de un solo idioma.'),
                Select::make('lang')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                    ])
                    ->default('es')
                    ->required()
                    ->native(false),
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->image()
                    ->disk(config('filesystems.tutorials_disk')),
                TextInput::make('image_alt')
                    ->label('Texto alternativo (alt) de la imagen')
                    ->helperText('Describe la imagen para SEO y accesibilidad. Ej: "Perro labrador jugando en el césped".')
                    ->maxLength(255),
                TextInput::make('level'),
                Toggle::make('is_published')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
