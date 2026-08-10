<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contenido')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->rule('regex:/^[a-z0-9-]+$/')
                            ->validationMessages([
                                'regex' => 'El slug solo acepta minúsculas, números y guiones.',
                            ])
                            ->helperText('Solo minúsculas, números y guiones. Ej: cuidados-del-jaguar'),
                        TextInput::make('translation_key')
                            ->label('Clave de traducción')
                            ->helperText('Mismo valor en las versiones es/en para enlazarlas. Vacío si es de un solo idioma.'),
                        Select::make('lang')
                            ->label('Idioma')
                            ->options(['es' => 'Español', 'en' => 'English'])
                            ->default('es')
                            ->required()
                            ->native(false),
                        Textarea::make('excerpt')
                            ->label('Extracto / resumen')
                            ->rows(2)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Contenido')
                            ->columnSpanFull(),
                    ]),

                Section::make('Clasificación e imagen')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->label('Nombre')->required(),
                                TextInput::make('slug')->required()->rule('regex:/^[a-z0-9-]+$/'),
                                Select::make('lang')->options(['es' => 'Español', 'en' => 'English'])->default('es'),
                            ]),
                        Select::make('tags')
                            ->label('Etiquetas')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        FileUpload::make('cover_image')
                            ->label('Imagen de portada')
                            ->image()
                            ->disk(config('filesystems.tutorials_disk'))
                            ->directory('posts'),
                        TextInput::make('image_alt')
                            ->label('Texto alternativo (alt) de la imagen')
                            ->helperText('Describe la imagen para SEO/accesibilidad.')
                            ->maxLength(255),
                    ]),

                Section::make('SEO')
                    ->description('Título y descripción para buscadores. Si los dejas vacíos, se usa el título y el extracto.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta título')
                            ->maxLength(60)
                            ->helperText('Ideal ≤ 60 caracteres.'),
                        TextInput::make('meta_description')
                            ->label('Meta descripción')
                            ->maxLength(160)
                            ->helperText('Ideal ≤ 160 caracteres.'),
                    ]),

                Section::make('Publicación')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Publicado')
                            ->helperText('Apagado = borrador (no se ve en el sitio).'),
                        DateTimePicker::make('published_at')
                            ->label('Fecha de publicación'),
                    ]),
            ]);
    }
}
