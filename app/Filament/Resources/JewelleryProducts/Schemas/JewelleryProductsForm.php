<?php

namespace App\Filament\Resources\JewelleryProducts\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use App\Models\Size;
use App\Models\Color;

class JewelleryProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('name')
                    ->label('Product Name')
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                RichEditor::make('terms')
                    ->label('Terms & Conditions')
                    ->columnSpanFull(),

                RichEditor::make('care_instructions')
                    ->label('Care Instructions')
                    ->columnSpanFull(),

              
                Repeater::make('jewelleryDetails')
                            ->relationship('jewelleryDetails')
                            ->columns(3)
                            ->label('Product Variants')
                            ->schema([
                                Select::make('size_id')
                                    ->label('Size')
                                    ->options(Size::where('type', 'jewellery')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('price')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('price_strike')
                                    ->numeric(),

                                TextInput::make('stock')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columnSpanFull()
                            ->grid(1)
                            ->addActionLabel('Add Variant')
                            ->defaultItems(1),
                    

                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(255),

                TextInput::make('meta_desc')
                    ->label('Meta Description')
                    ->maxLength(255),

                FileUpload::make('image')
                    ->label('Main Image')
                    ->image()
                    ->directory('products')
                    ->columnSpanFull(),

                FileUpload::make('additional_images')
                    ->label('Additional Images')
                    ->image()
                    ->multiple()
                    ->directory('products')
                    ->panelLayout('grid')
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
