<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Models\Category;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->columnSpanFull(),
                RichEditor::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                RichEditor::make('terms')
                    ->default(null)
                    ->columnSpanFull(),
                RichEditor::make('care_instructions')
                    ->default(null)
                    ->columnSpanFull(),

                Repeater::make('varients')
                    ->relationship('varients')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Variant Name')
                            ->placeholder('e.g. 20cm, 30cm')
                            ->required(),

                        TextInput::make('price')
                            ->numeric()
                            ->label('Price')
                            ->required(),

                        TextInput::make('stock')
                            ->numeric()
                            ->default(0)
                            ->label('Stock'),
                    ])
                    ->grid(1)
                    ->columnSpanFull()
                    ->defaultItems(1),
              
                TextInput::make('meta_title')
                    ->default(null),
                TextInput::make('meta_desc')
                    ->default(null),
                FileUpload::make('image')
                    ->image()
                    ->columnSpanFull(),
                FileUpload::make('additional_images')
                    ->image()
                    ->multiple()
                    ->columnSpanFull()
            ]);
    }
}
